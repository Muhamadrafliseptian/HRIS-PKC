<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AttendanceLogs;
use App\Models\Employee;
use App\Models\EmployeeShift;
use Carbon\Carbon;
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
        $start = Carbon::parse($this->date)->startOfDay();
        $end = Carbon::parse($this->date)->endOfDay();

        $logs = AttendanceLogs::where('device_id', $this->deviceId)
            ->whereBetween('scan_time', [$start, $end->copy()->addDay()])
            ->orderBy('scan_time')
            ->get();

        if ($logs->isEmpty()) {
            return;
        }

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

            foreach ($snapshot['segments'] as $segment) {

                $clockIn = $segment['clock_in'];
                $clockOut = $segment['clock_out'];

                $startShift = Carbon::parse($this->date . ' ' . $clockIn);
                $endShift = Carbon::parse($this->date . ' ' . $clockOut);

                if ($clockOut < $clockIn) {
                    $endShift->addDay();
                }

                $startWindow = $startShift->copy()->subMinutes(60);
                $endWindow = $endShift->copy()->addMinutes(60);

                $segmentLogs = $availableLogs->filter(function ($log) use ($startWindow, $endWindow) {
                    $time = Carbon::parse($log->scan_time);
                    return $time->between($startWindow, $endWindow);
                })->sortBy('scan_time')->values();

                if ($segmentLogs->isEmpty())
                    continue;

                $checkIn = $segmentLogs
                    ->filter(fn($log) => Carbon::parse($log->scan_time)->lte($startShift))
                    ->sortByDesc('scan_time')
                    ->first();

                if (!$checkIn) {
                    $checkIn = $segmentLogs
                        ->filter(fn($log) => Carbon::parse($log->scan_time)->gte($startShift))
                        ->sortBy('scan_time')
                        ->first();
                }
                $checkOut = $segmentLogs
                    ->filter(fn($log) => Carbon::parse($log->scan_time)->gte($endShift))
                    ->sortBy('scan_time')
                    ->first();

                if (!$checkOut) {
                    $checkOut = $segmentLogs
                        ->filter(fn($log) => Carbon::parse($log->scan_time)->lte($endShift))
                        ->sortByDesc('scan_time')
                        ->first();
                }
                $checkInTime = Carbon::parse($checkIn->scan_time);
                $checkOutTime = Carbon::parse($checkOut->scan_time);

                $totalMinutes = $checkInTime->diffInMinutes($checkOutTime);

                $late = 0;

                $grace = $startShift->copy()->addMinute();

                if ($checkInTime->gte($grace)) {
                    $late = $grace->diffInMinutes($checkInTime) + 1;
                }

                $early = 0;
                if ($checkOutTime->lt($endShift)) {
                    $early = $endShift->diffInMinutes($checkOutTime);
                }

                $status = 'present';

                if ($totalMinutes < 60)
                    $status = 'partial';
                if ($late > 0)
                    $status = 'late';

                $usedIds = $segmentLogs->pluck('id')->toArray();

                $availableLogs = $availableLogs
                    ->reject(fn($l) => in_array($l->id, $usedIds))
                    ->values();

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
        }

        if (empty($results)) {
            return;
        }

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
    }
}