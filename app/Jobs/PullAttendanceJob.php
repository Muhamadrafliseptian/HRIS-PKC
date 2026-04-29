<?php

namespace App\Jobs;

use App\Models\AttendanceLogs;
use App\Models\BiometricDevice;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
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

    public function handle(): void
    {
        Cache::put('pull_attendance_status', [
            'state' => 'processing',
            'message' => null
        ], now()->addMinutes(10));
        
        try {

            $device = BiometricDevice::where('branch', $this->branchId)->first();

            if (!$device) {
                throw new Exception('Device tidak ditemukan');
            }

            $response = Http::timeout(600)
                ->post('http://api-att-pkc.deveen.online/attendance', [
                    'ip' => $device->ip_address,
                    'port' => $device->port,
                    'periode' => $this->periode,
                ]);

            if (!$response->successful()) {
                throw new Exception('Python API error: ' . $response->body());
            }

            $json = json_decode($response->body(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON ERROR: ' . json_last_error_msg());
            }

            if (!$json || !isset($json['success']) || $json['success'] !== true) {
                throw new Exception('Invalid Python response structure');
            }

            $data = $json['data'] ?? [];

            if (empty($data)) {
                Cache::put('pull_attendance_status', 'no new data', now()->addMinutes(1));
                return;
            }

            $now = now();

            $logs = collect($data);

            if ($this->employeeServices) {

                $allowedUserIds = Employee::where('employee_services', $this->employeeServices)
                    ->pluck('user_id')
                    ->flip();

                if ($allowedUserIds->isEmpty()) {
                    throw new Exception('Tidak ada employee dengan service tersebut');
                }

                $logs = $logs->filter(function ($log) use ($allowedUserIds) {
                    return isset($allowedUserIds[$log['user_id']]);
                });
            }

            $logs = $logs->map(function ($log) use ($device, $now) {
                return [
                    'user_id' => $log['user_id'] ?? null,
                    'scan_time' => $log['timestamp'] ?? null,
                    'branch' => $device->branch,
                    'device_id' => $device->id,
                    'device_ip' => $device->ip_address,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->filter(fn($item) => $item['user_id'] && $item['scan_time']);

            if ($logs->isEmpty()) {
                Cache::put('pull_attendance_status', 'no valid data after filter', now()->addMinutes(1));
                return;
            }

            foreach ($logs->chunk(500) as $chunk) {
                AttendanceLogs::insertOrIgnore($chunk->toArray());
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

            Cache::put('pull_attendance_status', [
                'state' => 'done',
                'message' => 'success'
            ], now()->addMinutes(1));

        } catch (Exception $e) {
            Log::error('Pull Attendance Error', [
                'branch_id' => $this->branchId,
                'periode' => $this->periode,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Cache::put('pull_attendance_status', [
                'state' => 'failed',
                'message' => $e->getMessage()
            ], now()->addMinutes(1));
            throw $e;
        }
    }
}