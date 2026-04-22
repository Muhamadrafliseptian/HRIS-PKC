<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLogs;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeService;
use App\Models\EmployeeShift;
use App\Models\EmployeeStatus;
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

                default:
                    throw new Exception('Type tidak valid');
            }

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function preview(Request $request)
    {
        return match ($request->type) {
            'log' => inertia('Report/Attendance/Log/IndexPreview', [
                'data' => $this->getLogData($request),
            ]),

            'kehadiran' => inertia('Report/Attendance/Transaction/IndexPreview', [
                'data' => $this->getKehadiranData($request),
            ]),

            default => throw new Exception('Type tidak valid'),
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

    public function previewKehadiran(Request $request)
    {
        $data = $this->getKehadiranData($request);

        return view('pdf.attendance_rekap', [
            'data' => $data,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
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
                    ($snapshot['segments'][0]['clock_in'] ?? '-') .
                    ' - ' .
                    ($snapshot['segments'][0]['clock_out'] ?? '-'),

                'check_in' => $att?->first_scan_at,
                'check_out' => $att?->last_scan_at,

                'late_minutes' => $att?->late_minutes ?? 0,
                'early_out_minutes' => $att?->early_out_minutes ?? 0,
                'total_work_minutes' => $att?->total_work_minutes ?? 0,

                'status' => $att?->status ?? 'absent',
            ];
        })->groupBy('employee_id');
    }
}
