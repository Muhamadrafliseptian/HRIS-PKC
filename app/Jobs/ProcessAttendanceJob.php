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
    ) {}

    public function handle(): void
    {
        $start = Carbon::parse($this->date)->startOfDay();
        $end   = Carbon::parse($this->date)->endOfDay();

        Log::info('PROCESS START', [
            'date' => $this->date,
            'device' => $this->deviceId
        ]);

        // ================= GET LOGS =================
        $logs = AttendanceLogs::where('device_id', $this->deviceId)
            ->whereBetween('scan_time', [$start, $end->copy()->addDay()]) // handle overnight
            ->orderBy('scan_time')
            ->get();

        if ($logs->isEmpty()) {
            Log::info('NO LOGS');
            return;
        }

        // ================= MAP EMPLOYEE =================
        $employees = Employee::whereIn('user_id', $logs->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        $results = [];

        foreach ($logs->groupBy('user_id') as $userId => $userLogs) {

            $employee = $employees[$userId] ?? null;
            if (!$employee) continue;

            // ================= GET SHIFT =================
            $shift = EmployeeShift::where('employee_id', $employee->id)
                ->whereDate('date', $this->date)
                ->first();

            if (!$shift || !$shift->shift_snapshot) continue;

            $snapshot = is_string($shift->shift_snapshot)
                ? json_decode($shift->shift_snapshot, true)
                : $shift->shift_snapshot;

            if (!isset($snapshot['segments'])) continue;

            // 🔥 penting: logs bisa dipakai per segment
            $availableLogs = $userLogs->values();

            foreach ($snapshot['segments'] as $segment) {

                $clockIn  = $segment['clock_in'];
                $clockOut = $segment['clock_out'];

                $startShift = Carbon::parse($this->date . ' ' . $clockIn);
                $endShift   = Carbon::parse($this->date . ' ' . $clockOut);

                // ================= CROSS DAY =================
                if ($clockOut < $clockIn) {
                    $endShift->addDay();
                }

                // ================= WINDOW =================
                $startWindow = $startShift->copy()->subMinutes(60);
                $endWindow   = $endShift->copy()->addMinutes(60);

                // ================= FILTER LOG =================
                $segmentLogs = $availableLogs->filter(function ($log) use ($startWindow, $endWindow) {
                    $time = Carbon::parse($log->scan_time);
                    return $time->between($startWindow, $endWindow);
                })->sortBy('scan_time')->values();

                if ($segmentLogs->isEmpty()) continue;

                // ================= PICK IN/OUT =================
                $checkIn  = $segmentLogs->first();
                $checkOut = $segmentLogs->last();

                $checkInTime  = Carbon::parse($checkIn->scan_time);
                $checkOutTime = Carbon::parse($checkOut->scan_time);

                // ================= WORK MINUTES =================
                $totalMinutes = $checkInTime->diffInMinutes($checkOutTime);

                // ================= LATE =================
                $late = 0;
                if ($checkInTime->gt($startShift)) {
                    $late = $checkInTime->diffInMinutes($startShift);
                }

                // ================= EARLY OUT =================
                $early = 0;
                if ($checkOutTime->lt($endShift)) {
                    $early = $endShift->diffInMinutes($checkOutTime);
                }

                // ================= STATUS =================
                $status = 'present';

                if ($totalMinutes < 60) $status = 'partial';
                if ($late > 0) $status = 'late';

                // ================= REMOVE USED LOG =================
                $usedIds = $segmentLogs->pluck('id')->toArray();

                $availableLogs = $availableLogs
                    ->reject(fn($l) => in_array($l->id, $usedIds))
                    ->values();

                // ================= SAVE =================
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
            Log::info('NO ATTENDANCE GENERATED');
            return;
        }

        // ⚠️ PENTING: unique harus pakai shift_id juga
        foreach (array_chunk($results, 500) as $chunk) {
            Attendance::upsert(
                $chunk,
                ['employee', 'shift_id', 'date'], // 🔥 WAJIB
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