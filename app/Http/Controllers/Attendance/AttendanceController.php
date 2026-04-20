<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Jobs\PullAttendanceJob;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\Branch;
use App\Models\EmployeeShift;
use App\Models\EmployeeStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index()
    {
        try {
            $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(fn($b) => [
                'value' => $b->id,
                'label' => $b->name,
            ]);

            $status = EmployeeStatus::orderBy('id', 'desc')->get(['id', 'name'])->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name,
            ]);

            $shifts = EmployeeStatus::orderBy('id', 'desc')->get(['id', 'name'])->map(fn($s) => [
                'value' => $s->id,
                'label' => $s->name,
            ]);
            $months = collect(range(0, 11))->map(function ($i) {
                $date = now()->subMonths($i);
                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('F Y'),
                ];
            });

            return Inertia::render('Attendances/Index', [
                'branchs' => $branchs,
                'shifts' => $shifts,
                'status' => $status,
                'periods' => $months,
            ]);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function pull(Request $request)
    {
        try {

            $job = PullAttendanceJob::dispatch($request->branch, $request->periode);


            Cache::put('pull_attendance_status', 'processing', now()->addMinutes(10));

            return successHandler('Diproses di background');
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function pullStatus()
    {
        $status = Cache::get('pull_attendance_status', 'idle');
        return successHandler(['status' => $status]);
    }

    public function read(Request $request)
    {
        try {

            $periode = $request->periode
                ? Carbon::parse($request->periode . '-01')
                : now();

            $month = $periode->month;
            $year = $periode->year;
            $daysInMonth = $periode->daysInMonth;

            // ================= EXCEPTION =================
            $exceptions = AttendanceException::whereBetween('start_date', [
                $periode->copy()->startOfMonth(),
                $periode->copy()->endOfMonth()
            ])
                ->get()
                ->groupBy('employee');

            // ================= ATTENDANCE =================
            $data = Attendance::with(['dtemployee', 'dtbranch'])
                ->whereHas('dtemployee')
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->when($request->branch, fn($q) => $q->where('branch', $request->branch))
                ->get();

            // ================= SHIFT =================
            $employeeShifts = EmployeeShift::whereMonth('date', $month)
                ->whereYear('date', $year)
                ->get()
                ->groupBy('employee_id');

            // ================= GROUP =================
            $grouped = $data->groupBy('employee')->map(function ($empAtt) use ($daysInMonth, $periode, $employeeShifts, $exceptions) {

                $record = $empAtt->first();
                $employee = $record->dtemployee;
                $branch = $record->dtbranch;

                $attendance = [];

                $empShiftData = $employeeShifts[$record->employee] ?? collect();
                $empException = $exceptions[$record->employee] ?? collect();

                for ($day = 1; $day <= $daysInMonth; $day++) {

                    $date = $periode->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);

                    // ================= EXCEPTION CHECK =================
                    $exception = $empException->first(function ($ex) use ($date) {
                        return $date >= $ex->start_date && $date <= $ex->end_date;
                    });

                    if ($exception) {
                        $attendance[$day] = [
                            [
                                'type' => 'exception',
                                'status' => $exception->status,
                                'note' => $exception->note,
                            ]
                        ];
                        continue;
                    }

                    // ================= MULTI SHIFT =================
                    $dayRecords = $empAtt->where('date', $date)->values();

                    if ($dayRecords->isEmpty()) {
                        $attendance[$day] = [];
                        continue;
                    }

                    $dailyResult = [];

                    foreach ($dayRecords as $rec) {

                        $checkInTime = $rec->first_scan_at
                            ? Carbon::parse($rec->first_scan_at)
                            : null;

                        $checkOutTime = $rec->last_scan_at
                            ? Carbon::parse($rec->last_scan_at)
                            : null;

                        $shift = $empShiftData->firstWhere('shift_id', $rec->shift_id);
                        $snapshot = $shift?->shift_snapshot;

                        $isLate = false;
                        $isEarlyOut = false;
                        $lateMinutes = 0;

                        if ($snapshot && isset($snapshot['segments']) && $checkInTime) {

                            $segments = collect($snapshot['segments'])->sortBy('order')->values();

                            $first = $segments->first();
                            $last = $segments->last();

                            $tolerance = $snapshot['tolerance_after_in'] ?? 0;

                            // ===== LATE =====
                            if ($first) {
                                $start = Carbon::parse($date . ' ' . $first['clock_in']);
                                $allowed = $start->copy()->addMinutes($tolerance);

                                if ($checkInTime->gt($allowed)) {
                                    $isLate = true;
                                    $lateMinutes = $allowed->diffInMinutes($checkInTime);
                                }
                            }

                            // ===== EARLY OUT =====
                            if ($last && $checkOutTime) {

                                $end = Carbon::parse($date . ' ' . $last['clock_out']);

                                if ($last['clock_out'] < $last['clock_in']) {
                                    $end->addDay();
                                }

                                if ($checkOutTime->lt($end)) {
                                    $isEarlyOut = true;
                                }
                            }
                        }

                        $dailyResult[] = [
                            'type' => 'attendance',
                            'check_in' => $checkInTime?->format('H:i'),
                            'check_out' => $checkOutTime?->format('H:i'),
                            'work_minutes' => $rec->total_work_minutes,
                            'is_late' => $isLate,
                            'late_minutes' => $lateMinutes,
                            'is_early_out' => $isEarlyOut,
                            'shift' => $snapshot['code'] ?? null,
                        ];
                    }

                    $attendance[$day] = $dailyResult;
                }

                return [
                    'employee_id' => $employee?->id,
                    'user_id' => $employee?->user_id,
                    'nama' => $employee?->name,
                    'branch' => $branch?->name ?? null,
                    'attendance' => $attendance,
                ];
            })->values();

            return successHandler([
                'days_in_month' => $daysInMonth,
                'month' => $periode->translatedFormat('F Y'),
                'data' => $grouped,
            ]);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function changeStatusAttendance(Request $request)
    {
        try {

            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|string',
                'note' => 'nullable|string',
            ]);

            $employeeId = $validated['employee_id'];
            $start = Carbon::parse($validated['start_date'])->startOfDay();
            $end = Carbon::parse($validated['end_date'])->endOfDay();

            // ================= DELETE OLD =================
            AttendanceException::where('employee', $employeeId)
                ->whereBetween('start_date', [
                    $start->toDateString(),
                    $end->toDateString()
                ])
                ->delete();

            $rows = [];

            foreach ($start->copy()->daysUntil($end->copy()->addDay()) as $date) {

                $rows[] = [
                    'employee' => $employeeId,
                    'start_date' => $date->toDateString(),
                    'end_date' => $date->toDateString(),
                    'status' => $validated['status'],
                    'note' => $validated['note'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($rows, 100) as $chunk) {
                AttendanceException::insert($chunk);
            }

            return successHandler('Berhasil update note');

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }
}