<?php

namespace App\Jobs;

use App\Models\AttendanceLogs;
use App\Models\BiometricDevice;
use App\Jobs\ProcessAttendanceJob;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class PullAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(
        protected int $branchId,
        protected ?string $periode = null
    ) {}

    public function handle(): void
    {
        Cache::put('pull_attendance_status', 'processing', now()->addMinutes(10));

        try {

            $device = BiometricDevice::where('branch', $this->branchId)->first();

            if (!$device) {
                throw new \Exception('Device tidak ditemukan');
            }

            $lastPulled = AttendanceLogs::where('device_id', $device->id)
                ->max('scan_time');

            $command = [
                'python3',
                base_path('app/Helpers/Attendance.py'),
                $device->ip_address,
                (string) $device->port,
                $this->periode ?? '',
                $lastPulled ?? ''
            ];

            $result = Process::timeout(120)->run($command);

            Log::info('PYTHON OUTPUT', [
                'output' => $result->output(),
                'error' => $result->errorOutput(),
            ]);

            if ($result->failed()) {
                throw new \Exception($result->errorOutput() ?: $result->output());
            }

            $json = json_decode(trim($result->output()), true);

            if (!$json || !($json['success'] ?? false)) {
                throw new \Exception('Invalid Python response');
            }

            $data = $json['data'] ?? [];

            if (empty($data)) {
                return;
            }

            $now = now();

            $logs = collect($data)->map(fn($log) => [
                'user_id'   => $log['user_id'],
                'scan_time' => $log['timestamp'],
                'branch'    => $device->branch,
                'device_id' => $device->id,
                'device_ip' => $device->ip_address,
                'created_at'=> $now,
                'updated_at'=> $now,
            ]);

            foreach ($logs->chunk(500) as $chunk) {
                AttendanceLogs::insertOrIgnore($chunk->toArray());
            }

            // group per HARI
            $dates = $logs->pluck('scan_time')
                ->map(fn($t) => Carbon::parse($t)->toDateString())
                ->unique();

            foreach ($dates as $date) {
                ProcessAttendanceJob::dispatch(
                    deviceId: $device->id,
                    date: $date
                );
            }

            Cache::put('pull_attendance_status', 'done', now()->addMinutes(1));

        } catch (\Exception $e) {

            Log::error('PULL FAILED', [
                'message' => $e->getMessage(),
            ]);

            Cache::put('pull_attendance_status', $e->getMessage(), now()->addMinutes(1));

            throw $e;
        }
    }
}