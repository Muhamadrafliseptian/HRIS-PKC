<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceLogs;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $deviceId,
        protected string $date
    ) {
    }
    public function handle(): void
    {
        try {
            $start = Carbon::parse($this->date)->startOfDay();
            $end = Carbon::parse($this->date)->endOfDay();

            $logs = AttendanceLogs::where('device_id', $this->deviceId)
                ->whereBetween('scan_time', [$start, $end])
                ->orderBy('scan_time')
                ->get();

            if ($logs->isEmpty())
                return;

            $employees = Employee::whereIn('user_id', $logs->pluck('user_id'))
                ->get()
                ->keyBy('user_id');

            $results = [];

            foreach ($logs->groupBy('user_id') as $userId => $userLogs) {

                $employee = $employees[$userId] ?? null;
                if (!$employee)
                    continue;

                $shift = EmployeeShift::where('employee_id', $employee->id)
                    ->whereDate('date', $this->date)
                    ->first();

                if (!$shift || !$shift->shift_snapshot)
                    continue;

                $snapshot = is_string($shift->shift_snapshot)
                    ? json_decode($shift->shift_snapshot, true)
                    : $shift->shift_snapshot;

                if (!isset($snapshot['segments']))
                    continue;

                $availableLogs = $userLogs->values();

                if (($snapshot['type'] ?? '') === 'split') {

                    $segments = $snapshot['segments'];
                    $first = $segments[0];
                    $last = $segments[count($segments) - 1];

                    $startShift = Carbon::parse($this->date . ' ' . $first['clock_in']);
                    $endShift = Carbon::parse($this->date . ' ' . $last['clock_out']);

                    if ($last['clock_out'] < $first['clock_in']) {
                        $endShift->addDay();
                    }

                    $startWindow = $startShift->copy()->subHours(6);
                    $endWindow = $endShift->copy()->addHours(6);

                    $segmentLogs = $availableLogs
                        ->filter(
                            fn($log) =>
                            Carbon::parse($log->scan_time)->between($startWindow, $endWindow)
                        )
                        ->sortBy('scan_time')
                        ->values();

                    if ($segmentLogs->isEmpty())
                        continue;

                    $checkInTime = Carbon::parse($segmentLogs->first()->scan_time);
                    $checkOutTime = Carbon::parse($segmentLogs->last()->scan_time);

                } else {
                    foreach ($snapshot['segments'] as $segment) {

                        $startShift = Carbon::parse($this->date . ' ' . $segment['clock_in']);
                        $endShift = Carbon::parse($this->date . ' ' . $segment['clock_out']);

                        if ($segment['clock_out'] < $segment['clock_in']) {
                            $endShift->addDay();
                        }

                        $startWindow = $startShift->copy()->subHours(4);
                        $endWindow = $endShift->copy()->addHours(6);

                        $segmentLogs = $availableLogs
                            ->filter(
                                fn($log) =>
                                Carbon::parse($log->scan_time)->between($startWindow, $endWindow)
                            )
                            ->sortBy('scan_time')
                            ->values();

                        if ($segmentLogs->isEmpty())
                            continue;

                        $checkIn = $segmentLogs
                            ->sortBy(
                                fn($log) =>
                                abs(Carbon::parse($log->scan_time)->diffInSeconds($startShift))
                            )
                            ->first();

                        $checkOut = $segmentLogs
                            ->filter(
                                fn($log) =>
                                Carbon::parse($log->scan_time)->gte($endShift)
                            )
                            ->sortBy('scan_time')
                            ->first();

                        if (!$checkOut) {
                            $checkOut = $segmentLogs
                                ->filter(
                                    fn($log) =>
                                    Carbon::parse($log->scan_time)->lte($endShift)
                                )
                                ->sortByDesc('scan_time')
                                ->first();
                        }

                        if (!$checkOut || $checkOut->id === $checkIn->id) {
                            $checkOut = $segmentLogs->last();
                        }

                        $checkInTime = Carbon::parse($checkIn->scan_time);
                        $checkOutTime = Carbon::parse($checkOut->scan_time);

                        break;
                    }
                }

                if (!isset($checkInTime) || !isset($checkOutTime))
                    continue;

                $totalMinutes = $checkInTime->diffInMinutes($checkOutTime);

                $late = $checkInTime->gt($startShift)
                    ? $startShift->diffInMinutes($checkInTime)
                    : 0;

                $early = $checkOutTime->lt($endShift)
                    ? $endShift->diffInMinutes($checkOutTime)
                    : 0;

                $status = 'present';

                if ($totalMinutes < 60) {
                    $status = 'partial';
                }

                if ($late > 0) {
                    $status = 'late';
                }

                if ($checkInTime->eq($checkOutTime)) {
                    $status = 'partial';
                }

                $results[] = [
                    'employee' => $employee->id,
                    'branch' => $employee->branch,
                    'shift_id' => $shift->shift_id,
                    'date' => $this->date,

                    'first_scan_at' => $checkInTime,
                    'last_scan_at' => $checkOutTime,

                    'total_work_minutes' => $totalMinutes,
                    'late_minutes' => $late,
                    'early_out_minutes' => $early,
                    'status' => $status,

                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($results))
                return;

            foreach (array_chunk($results, 500) as $chunk) {
                Attendance::upsert(
                    $chunk,
                    ['employee', 'shift_id', 'date'],
                    [
                        'first_scan_at',
                        'last_scan_at',
                        'total_work_minutes',
                        'late_minutes',
                        'early_out_minutes',
                        'status',
                        'updated_at'
                    ]
                );
            }
        } catch (Exception $e) {
            Log::error("ATTENDANCE JOB ERROR", [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}