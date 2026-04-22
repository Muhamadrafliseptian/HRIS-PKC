<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Jobs\PullAttendanceJob;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\Branch;
use App\Models\EmployeeService;
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

            $services = EmployeeService::orderBy('id', 'desc')->get(['id', 'name'])->map(fn($s) => [
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
                'services' => $services,
                'periods' => $months,
            ]);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function pull(Request $request)
    {
        try {

            $job = PullAttendanceJob::dispatch($request->branch, $request->periode, $request->employee_services);


            Cache::put('pull_attendance_status', 'processing', now()->addMinutes(10));

            return successHandler();
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
            $periode = $this->resolvePeriode($request);

            $data = $this->buildAttendanceReport($request, $periode);

            return successHandler($data);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    private function resolvePeriode($request)
    {
        return $request->periode
            ? Carbon::parse($request->periode . '-01')
            : now();
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

    private function buildAttendanceReport($request, $periode)
    {
        $month = $periode->month;
        $year = $periode->year;
        $daysInMonth = $periode->daysInMonth;

        $exceptions = $this->getExceptions($periode);
        $attendances = $this->getAttendances($request, $month, $year);
        $shifts = $this->getShifts($month, $year);

        $grouped = $attendances->groupBy('employee')
            ->map(fn($empAtt) => $this->mapEmployee(
                $empAtt,
                $periode,
                $daysInMonth,
                $shifts,
                $exceptions
            ))
            ->sortBy('nama')
            ->values();

        return [
            'days_in_month' => $daysInMonth,
            'month' => $periode->translatedFormat('F Y'),
            'data' => $grouped,
        ];
    }

    private function getExceptions($periode)
    {
        return AttendanceException::whereBetween('start_date', [
            $periode->copy()->startOfMonth(),
            $periode->copy()->endOfMonth()
        ])->get()->groupBy('employee');
    }

    private function getAttendances($request, $month, $year)
    {
        return Attendance::with(['dtemployee', 'dtbranch'])
            ->whereHas('dtemployee')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->when($request->branch, fn($q) => $q->where('branch', $request->branch))
            ->get();
    }

    private function getShifts($month, $year)
    {
        return EmployeeShift::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');
    }

    private function mapEmployee($empAtt, $periode, $daysInMonth, $shifts, $exceptions)
    {
        $record = $empAtt->first();

        $employee = $record->dtemployee;
        $branch = $record->dtbranch;

        $empShift = $shifts[$record->employee] ?? collect();
        $empException = $exceptions[$record->employee] ?? collect();

        $attendance = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $periode->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);

            $attendance[$day] = $this->mapDaily(
                $date,
                $empAtt,
                $empShift,
                $empException
            );
        }

        return [
            'employee_id' => $employee?->id,
            'user_id' => $employee?->user_id,
            'nama' => $employee?->name,
            'branch' => $branch?->name,
            'attendance' => $attendance,
        ];
    }

    private function mapDaily($date, $empAtt, $empShift, $empException)
    {
        $exception = $empException->first(
            fn($ex) =>
            $date >= $ex->start_date && $date <= $ex->end_date
        );

        if ($exception) {
            return [
                [
                    'type' => 'exception',
                    'status' => $exception->status,
                    'note' => $exception->note,
                ]
            ];
        }

        $dayRecords = $empAtt->where('date', $date)->values();

        if ($dayRecords->isEmpty()) {
            return [];
        }

        return $dayRecords->map(function ($rec) use ($date, $empShift) {
            return $this->transformAttendance($rec, $date, $empShift);
        })->toArray();
    }

    private function transformAttendance($rec, $date, $empShift)
    {
        $checkIn = $rec->first_scan_at ? Carbon::parse($rec->first_scan_at) : null;
        $checkOut = $rec->last_scan_at ? Carbon::parse($rec->last_scan_at) : null;

        $shift = $empShift->firstWhere('shift_id', $rec->shift_id);
        $snapshot = $shift?->shift_snapshot;

        [$isLate, $lateMinutes] = $this->calculateLate($checkIn, $date, $snapshot);
        $isEarly = $this->calculateEarlyOut($checkOut, $date, $snapshot);

        return [
            'type' => 'attendance',
            'check_in' => $checkIn?->format('H:i:s'),
            'check_out' => $checkOut?->format('H:i:s'),
            'work_minutes' => $rec->total_work_minutes,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_early_out' => $isEarly,
            'shift' => $snapshot['code'] ?? null,
        ];
    }

    private function calculateLate($checkIn, $date, $snapshot)
    {
        if (!$checkIn || !$snapshot || !isset($snapshot['segments'])) {
            return [false, 0];
        }

        $first = collect($snapshot['segments'])->sortBy('order')->first();

        if (!$first)
            return [false, 0];

        $start = Carbon::parse($date . ' ' . $first['clock_in']);

        $lateMinutes = $start->diffInMinutes($checkIn);

        if ($lateMinutes >= 1) {
            return [true, $lateMinutes];
        }

        return [false, 0];
    }

    private function calculateEarlyOut($checkOut, $date, $snapshot)
    {
        if (!$checkOut || !$snapshot || !isset($snapshot['segments'])) {
            return false;
        }

        $last = collect($snapshot['segments'])->sortByDesc('order')->first();

        if (!$last)
            return false;

        $end = Carbon::parse($date . ' ' . $last['clock_out']);

        if ($last['clock_out'] < $last['clock_in']) {
            $end->addDay();
        }

        return $checkOut->lt($end);
    }
}