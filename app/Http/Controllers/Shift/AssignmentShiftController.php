<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeShift;
use App\Models\ShiftCategory;
use App\Models\Shifts;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentShiftController extends Controller
{
    public function index()
    {
        try {
            $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(function ($branchs) {
                return [
                    'value' => $branchs->id,
                    'label' => $branchs->name
                ];
            });

            $shiftCategory = ShiftCategory::get(['id', 'name'])->map(function ($shiftCategory) {
                return [
                    'value' => $shiftCategory->id,
                    'label' => $shiftCategory->name
                ];
            });

            $status = [
                [
                    'value' => 0,
                    'label' => "Inactive",
                ],
                [
                    'value' => 1,
                    'label' => "Active",
                ],
            ];

            $periods = [];
            $start = Carbon::now()->startOfMonth();

            for ($i = 0; $i < 6; $i++) {
                $date = $start->copy()->addMonths($i);

                $periods[] = [
                    'value' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('F Y'),
                ];
            }

            $response = [
                'branchs' => $branchs,
                'status' => $status,
                'shift_categories' => $shiftCategory,
                'periods' => $periods
            ];

            return Inertia::render('Shift/Assign/IndexAssignmentShift', $response);

        } catch (Exception $err) {

        }
    }

    public function read(Request $request)
    {
        try {
            $request->validate([
                'branch' => 'required',
                'month' => 'required|date_format:Y-m',
            ]);

            $employees = Employee::query()
                ->when($request->branch, function ($q) use ($request) {
                    $q->where('branch', $request->branch);
                })
                ->orderByDesc('id')
                ->get([
                    'id',
                    'name',
                    'branch'
                ]);

            $shifts = Shifts::where('is_active', 1)
                ->get([
                    'id',
                    'code',
                    'name',
                    'shift_category',
                    'type'
                ]);

            $month = Carbon::parse($request->month);

            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $employeeShifts = EmployeeShift::whereBetween('date', [$start, $end])
                ->get([
                    'id',
                    'employee_id',
                    'shift_id',
                    'date'
                ]);

            $response = [
                'employees' => $employees,
                'shifts' => $shifts,
                'employee_shifts' => $employeeShifts,
            ];

            return successHandler($response);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required',
                'month' => 'required',
                'branch' => 'required'
            ]);

            $data = is_array($request->data)
                ? $request->data
                : json_decode($request->data, true);

            if (!is_array($data)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Format data tidak valid'
                ]);
            }

            $rows = [];

            foreach ($data as $item) {

                $shift = Shifts::with('details')->find($item['shift_id']);
                if (!$shift)
                    continue; // safety

                $rows[] = [
                    'employee_id' => $item['employee_id'],
                    'date' => $item['date'],
                    'shift_id' => $item['shift_id'],
                    'branch' => $request->branch,
                    'shift_snapshot' => json_encode([
                        'code' => $shift->code,
                        'name' => $shift->name,
                        'type' => $shift->type,
                        'segments' => $shift->details->map(function ($d) {
                            return [
                                'clock_in' => $d->clock_in,
                                'clock_out' => $d->clock_out,
                            ];
                        })->values(),
                    ]),
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            if (!empty($rows)) {
                EmployeeShift::upsert(
                    $rows,
                    ['employee_id', 'date'],
                    ['shift_id', 'shift_snapshot', 'updated_at']
                );
            }

            return successHandler([], 'Berhasil assign shift');

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }
}
