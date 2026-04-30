<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceException;
use App\Models\AttendanceLogs;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeService;
use App\Models\EmployeeShift;
use App\Models\EmployeeStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
class AttendanceReportController extends Controller
{
    public function indexLog()
    {
        try {
            $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(function ($branchs) {
                return [
                    'value' => $branchs->id,
                    'label' => $branchs->name
                ];
            });

            $category = EmployeeStatus::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name
                ];
            });

            $service = EmployeeService::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($service) {
                return [
                    'value' => $service->id,
                    'label' => $service->name
                ];
            });

            $device = BiometricDevice::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name
                ];
            });

            $response = [
                'branchs' => $branchs,
                'categories' => $category,
                'services' => $service,
                'devices' => $device
            ];

            return Inertia::render('Report/Attendance/Log/Index', $response);
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function indexTransaction()
    {
        try {
            $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(function ($branchs) {
                return [
                    'value' => $branchs->id,
                    'label' => $branchs->name
                ];
            });

            $category = EmployeeStatus::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name
                ];
            });

            $device = BiometricDevice::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name
                ];
            });

            $response = [
                'branchs' => $branchs,
                'categories' => $category,
                'devices' => $device
            ];

            return Inertia::render('Report/Attendance/Transaction/Index', $response);
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function download(Request $request)
    {
        try {

            switch ($request->type) {
                case 'log':
                    return $this->downloadLog($request);

                case 'kehadiran':
                    return $this->downloadKehadiran($request);

                case 'rekap':
                    return $this->downloadRekap($request);
                default:
                    throw new Exception('Type tidak valid');
            }

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    private function downloadRekap(Request $request)
    {
        try {
            $data = $this->getRekap($request);

            $pdf = Pdf::loadView('pdf.attendance_rekapitulasi', [
                'data' => $data,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);
    
            return $pdf->download('attendance_rekapitulasi.pdf');
        } catch (Exception $err) {

        }
    }

    public function preview(Request $request)
    {
        return match ($request->type) {
            'log' => (function () use ($request) {
                    $data = $this->getLogData($request);

                    return inertia('Report/Attendance/Log/IndexPreview', [
                    'data' => $data,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    ]);
                })(),

            'kehadiran' => inertia('Report/Attendance/Transaction/IndexPreview', [
                'data' => $this->getKehadiranData($request),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]),

            'rekap' => inertia('Report/Attendance/Rekap/IndexPreview', [
                'data' => $this->getRekap($request),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]),

            default => throw new \Exception('Type tidak valid'),
        };
    }

    public function downloadLog(Request $request)
    {
        $logs = $this->getLogData($request);

        $pdf = Pdf::loadView('pdf.attendance', [
            'logs' => $logs,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->download('attendance_log.pdf');
    }

    public function downloadKehadiran(Request $request)
    {
        $data = $this->getKehadiranData($request);

        $pdf = Pdf::loadView('pdf.attendance_rekap', [
            'data' => $data,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->download('attendance_rekap.pdf');
    }

    private function getLogData($request)
    {
        $ids = json_decode($request->ids, true);

        $query = AttendanceLogs::with([
            'dtbiouser.biometricUser.device',
            'dtbiouser.dtbranch'
        ]);

        if (!empty($ids)) {
            $userIds = Employee::whereIn('id', $ids)
                ->pluck('user_id');

            $query->whereIn('user_id', $userIds);
        }

        if ($request->branch) {
            $query->where('branch', $request->branch);
        }

        if ($request->device_id) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('scan_time', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $logs = $query->orderBy('scan_time', 'asc')->get();

        return $logs->groupBy('user_id')->map(function ($items) {
            return $items->values();
        });
    }

    private function getKehadiranData($request)
    {
        $ids = json_decode($request->ids, true);

        $shiftQuery = EmployeeShift::with('employee');

        if (!empty($ids)) {
            $shiftQuery->whereIn('employee_id', $ids);
        }

        if ($request->branch) {
            $shiftQuery->where('branch', $request->branch);
        }

        if ($request->start_date && $request->end_date) {
            $shiftQuery->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $shifts = $shiftQuery->orderBy('date')->get();

        $attendance = Attendance::whereBetween('date', [
            $request->start_date,
            $request->end_date
        ])->get()->keyBy(
                fn($item) =>
                $item->employee . '_' . \Carbon\Carbon::parse($item->date)->format('Y-m-d')
            );

        return $shifts->map(function ($shift) use ($attendance) {

            $key = $shift->employee_id . '_' . \Carbon\Carbon::parse($shift->date)->format('Y-m-d');
            $att = $attendance[$key] ?? null;

            $snapshot = is_string($shift->shift_snapshot)
                ? json_decode($shift->shift_snapshot, true)
                : $shift->shift_snapshot;

            return (object) [
                'employee_id' => $shift->employee_id,
                'employee_name' => $shift->employee->name ?? '-',
                'date' => $shift->date,
                'is_holiday' => $shift->is_holiday,

                'jam_kerja' =>
                    !empty($snapshot['segments'])
                    ? ($snapshot['segments'][0]['clock_in'] ?? '-') . ' - ' . ($snapshot['segments'][0]['clock_out'] ?? '-')
                    : 'OFF',

                'check_in' => $att?->first_scan_at,
                'check_out' => $att?->last_scan_at,

                'late_minutes' => $att?->late_minutes ?? 0,
                'early_out_minutes' => $att?->early_out_minutes ?? 0,
                'total_work_minutes' => $att?->total_work_minutes ?? 0,

                'status' => $this->resolveStatus($snapshot, $att),
            ];
        })->groupBy('employee_id');
    }

    public function getRekap($request)
    {
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);

        $ids = json_decode($request->ids, true);

        $employeeQuery = Employee::query()->select('id', 'name');

        if (!empty($ids)) {
            $employeeQuery->whereIn('id', $ids);
        }

        if ($request->branch) {
            $employeeQuery->where('branch', $request->branch);
        }

        $employees = $employeeQuery->get()->keyBy('id');

        $attendances = Attendance::whereBetween('date', [$start, $end])
            ->get()
            ->groupBy('employee');

        $exceptions = AttendanceException::where(function ($q) use ($start, $end) {
            $q->whereBetween('start_date', [$start, $end])
                ->orWhereBetween('end_date', [$start, $end]);
        })
            ->get()
            ->groupBy('employee');

        $days = $start->diffInDays($end) + 1;

        $result = [];

        foreach ($employees as $employee) {

            $empId = $employee->id;

            $empAttendance = $attendances[$empId] ?? collect();
            $empException = $exceptions[$empId] ?? collect();

            $attendanceByDate = $empAttendance->groupBy(function ($att) {
                return Carbon::parse($att->date)->format('Y-m-d');
            });

            $hadir = 0;
            $terlambat = 0;
            $pulangCepat = 0;

            foreach ($attendanceByDate as $date => $attList) {

                $att = $attList->first();

                $hadir++;

                if (($att->late_minutes ?? 0) > 0) {
                    $terlambat++;
                }

                if (($att->early_out_minutes ?? 0) < 0) {
                    $pulangCepat++;
                }
            }

            $DLAW = 0;
            $DLAK = 0;
            $DLP = 0;

            $IJIN1 = 0;
            $IJIN2 = 0;
            $I = 0;

            $sakit = 0;
            $cuti = 0;

            foreach ($empException as $ex) {

                $daysCount = $this->countDays($ex, $start, $end);

                switch ($ex->status) {
                    case 'DLAW':
                        $DLAW += $daysCount;
                        break;
                    case 'DLAK':
                        $DLAK += $daysCount;
                        break;
                    case 'DLP':
                        $DLP += $daysCount;
                        break;

                    case 'IJIN1':
                        $IJIN1 += $daysCount;
                        break;
                    case 'IJIN2':
                        $IJIN2 += $daysCount;
                        break;
                    case 'I':
                        $I += $daysCount;
                        break;

                    case 'S':
                        $sakit += $daysCount;
                        break;
                    case 'CT':
                        $cuti += $daysCount;
                        break;
                }
            }

            $izin = $IJIN1 + $IJIN2 + $I + $DLAW + $DLAK + $DLP;

            $alpha = max(0, $days - ($hadir + $izin + $sakit + $cuti));

            $result[] = [
                'employee_id' => $empId,
                'name' => $employee->name,

                'total_hari' => $days,
                'hadir' => $hadir,
                'alpha' => $alpha,

                'terlambat' => $terlambat,
                'pulang_cepat' => $pulangCepat,

                'DLAW' => $DLAW,
                'DLAK' => $DLAK,
                'DLP' => $DLP,

                'IJIN1' => $IJIN1,
                'IJIN2' => $IJIN2,
                'I' => $I,

                'izin_total' => $izin,
                'sakit' => $sakit,
                'cuti' => $cuti,
            ];
        }

        return $result;
    }

    private function countDays($exception, $start, $end)
    {
        $startDate = Carbon::parse($exception->start_date);
        $endDate = Carbon::parse($exception->end_date);

        $realStart = $startDate->greaterThan($start) ? $startDate : $start;
        $realEnd = $endDate->lessThan($end) ? $endDate : $end;

        if ($realStart->gt($realEnd)) {
            return 0;
        }

        return $realStart->diffInDays($realEnd) + 1;
    }

    private function resolveStatus($snapshot, $att)
    {
        if (
            ($snapshot['code'] ?? null) === 'OFF' ||
            empty($snapshot['segments'])
        ) {
            return 'off';
        }

        if ($att) {
            return $att->status;
        }

        return 'absent';
    }
}
