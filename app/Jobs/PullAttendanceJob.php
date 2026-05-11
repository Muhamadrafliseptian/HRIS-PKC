<?php

namespace App\Jobs;

use App\Models\AttendanceLogs;
use App\Models\BiometricDevice;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class PullAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $branchId;
    protected ?string $periode;
    protected ?string $employeeServices;

    public function __construct(
        int $branchId,
        ?string $periode = null,
        ?string $employeeServices = null,
    ) {
        $this->branchId = $branchId;
        $this->periode = $periode;
        $this->employeeServices = $employeeServices;
    }

    // public function handle(): void
    // {
    //     Cache::put('pull_attendance_status', [
    //         'state' => 'processing',
    //         'message' => null
    //     ], now()->addMinutes(10));

    //     try {

    //         $device = BiometricDevice::where('branch', $this->branchId)->first();

    //         if (!$device) {
    //             throw new Exception('Device tidak ditemukan');
    //         }

    //         $response = Http::timeout(600)
    //             ->withHeaders([
    //                 'x-api-key' => env('ZK_API_KEY')
    //             ])
    //             ->post('http://127.0.0.1:8001/attendance', [
    //                 'ip' => $device->ip_address,
    //                 'port' => $device->port,
    //                 'periode' => $this->periode,
    //                 'last_pull' => optional($device->last_pull_at)->toDateTimeString()
    //             ]);

    //         if (!$response->successful()) {
    //             throw new Exception('Python API error: ' . $response->body());
    //         }

    //         $json = json_decode($response->body(), true);

    //         if (json_last_error() !== JSON_ERROR_NONE) {
    //             throw new Exception('JSON ERROR: ' . json_last_error_msg());
    //         }

    //         if (!$json || !isset($json['success']) || $json['success'] !== true) {
    //             throw new Exception('Invalid Python response structure');
    //         }

    //         $data = $json['data'] ?? [];

    //         if (empty($data)) {
    //             Cache::put('pull_attendance_status', 'no new data', now()->addMinutes(1));
    //             return;
    //         }

    //         $now = now();

    //         $logs = collect($data);

    //         if ($this->employeeServices) {

    //             $allowedUserIds = Employee::where('employee_services', $this->employeeServices)
    //                 ->pluck('user_id')
    //                 ->flip();

    //             if ($allowedUserIds->isEmpty()) {
    //                 throw new Exception('Tidak ada employee dengan service tersebut');
    //             }

    //             $logs = $logs->filter(function ($log) use ($allowedUserIds) {
    //                 return isset($allowedUserIds[$log['user_id']]);
    //             });
    //         }

    //         $logs = $logs->map(function ($log) use ($device, $now) {
    //             return [
    //                 'user_id' => $log['user_id'] ?? null,
    //                 'scan_time' => $log['timestamp'] ?? null,
    //                 'branch' => $device->branch,
    //                 'device_id' => $device->id,
    //                 'device_ip' => $device->ip_address,
    //                 'created_at' => $now,
    //                 'updated_at' => $now,
    //             ];
    //         })->filter(fn($item) => $item['user_id'] && $item['scan_time']);

    //         if ($logs->isEmpty()) {
    //             Cache::put('pull_attendance_status', 'no valid data after filter', now()->addMinutes(1));
    //             return;
    //         }

    //         foreach ($logs->chunk(500) as $chunk) {
    //             AttendanceLogs::insertOrIgnore($chunk->toArray());
    //         }

    //         $latestScanTime = $logs
    //             ->pluck('scan_time')
    //             ->map(fn($t) => Carbon::parse($t))
    //             ->max();

    //         $dates = $logs->pluck('scan_time')
    //             ->map(fn($t) => Carbon::parse($t)->toDateString())
    //             ->unique();

    //         foreach ($dates as $date) {
    //             ProcessAttendanceJob::dispatch(
    //                 deviceId: $device->id,
    //                 date: $date
    //             );
    //         }

    //         Cache::put('pull_attendance_status', [
    //             'state' => 'done',
    //             'message' => 'success'
    //         ], now()->addMinutes(1));

    //         DB::transaction(function () use ($device, $latestScanTime) {
    //             if ($latestScanTime) {
    //                 $device->update([
    //                     'last_pull_at' => $latestScanTime
    //                 ]);
    //             }
    //         });

    //     } catch (Exception $e) {
    //         Cache::put('pull_attendance_status', [
    //             'state' => 'failed',
    //             'message' => $e->getMessage()
    //         ], now()->addMinutes(1));
    //         throw $e;
    //     }
    // }

    public function handle(): void
    {
        try {

            $devices = $this->getDevices();

            foreach ($devices as $device) {

                $logs = $this->pullLogsFromDevice($device);

                if ($logs->isEmpty()) {
                    continue;
                }

                $this->storeLogs($logs, $device);

                $this->dispatchAttendanceProcess($logs, $device);
            }

            Cache::put('pull_attendance_status', [
                'state' => 'done',
                'message' => 'success'
            ], now()->addMinutes(1));

        } catch (Exception $e) {
            Cache::put('pull_attendance_status', [
                'state' => 'failed',
                'message' => $e->getMessage()
            ], now()->addMinutes(10));
            throw $e;
        }
    }

    private function getDevices()
    {
        $mobileBranches = [9, 10];

        if (in_array($this->branchId, $mobileBranches)) {
            return BiometricDevice::get();
        }

        return BiometricDevice::where('branch', $this->branchId)->get();
    }
    private function pullLogsFromDevice(BiometricDevice $device)
    {
        $response = Http::timeout(600)
            ->withHeaders([
                'x-api-key' => env('ZK_API_KEY')
            ])
            ->post('http://127.0.0.1:8001/attendance', [
                'ip' => $device->ip_address,
                'port' => $device->port,
                'periode' => $this->periode,
                'last_pull' => optional($device->last_pull_at)->toDateTimeString()
            ]);

        if (!$response->successful()) {
            return collect();
        }

        $json = $response->json();

        if (!($json['success'] ?? false)) {
            return collect();
        }

        $logs = collect($json['data'] ?? []);

        if ($this->employeeServices) {

            $allowedUserIds = Employee::where(
                'employee_services',
                $this->employeeServices
            )->pluck('user_id')->flip();

            $logs = $logs->filter(function ($log) use ($allowedUserIds) {

                if (!is_array($log)) {
                    return false;
                }

                return isset($allowedUserIds[$log['user_id'] ?? null]);
            });
        }

        $employeeBranches = Employee::pluck('branch', 'user_id');

        return $logs
            ->filter(fn($log) => is_array($log))
            ->map(function ($log) use ($device, $employeeBranches) {

                $userId = $log['user_id'] ?? null;

                return [
                    'user_id' => $userId,
                    'scan_time' => $log['timestamp'] ?? null,

                    'branch' => $employeeBranches[$userId] ?? $device->branch,

                    'device_id' => $device->id,
                    'device_ip' => $device->ip_address,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            })
            ->filter(
                fn($item) =>
                $item['user_id'] && $item['scan_time']
            );
    }

    private function storeLogs($logs, BiometricDevice $device): void
    {
        if ($logs->isEmpty()) {
            return;
        }

        foreach ($logs->chunk(500) as $chunk) {
            AttendanceLogs::insertOrIgnore($chunk->toArray());
        }

        $latestScanTime = $logs
            ->pluck('scan_time')
            ->map(fn($t) => Carbon::parse($t))
            ->max();

        if ($latestScanTime) {
            $device->update([
                'last_pull_at' => $latestScanTime
            ]);
        }
    }

    private function dispatchAttendanceProcess($logs, BiometricDevice $device): void
    {
        if ($logs->isEmpty()) {
            return;
        }

        $dates = $logs->pluck('scan_time')
            ->map(fn($t) => Carbon::parse($t)->toDateString())
            ->unique();

        foreach ($dates as $date) {

            ProcessAttendanceJob::dispatch(
                deviceId: $device->id,
                date: $date
            );
        }


    }
}