<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ShiftDetail;
use App\Models\Shifts;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index()
    {
        $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(function ($branchs) {
            return [
                'value' => $branchs->id,
                'label' => $branchs->name
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

        $response = [
            'branchs' => $branchs,
            'status' => $status
        ];

        return Inertia::render('Shift/Master/Index', $response);
    }

    public function read(Request $request)
    {
        try {
            $page = $request->page;
            $per_page = $request->per_page;
            $query = Shifts::with(['branch', 'details', 'category', 'employeeShifts']);

            $shiftPaginated = $query->orderBy('created_at', 'desc')
                ->paginate($per_page, ['*'], 'page', $page);

            $response = [
                'shifts' => $shiftPaginated->items(),
                'meta' => [
                    'current_page' => $shiftPaginated->currentPage(),
                    'last_page' => $shiftPaginated->lastPage(),
                    'per_page' => $shiftPaginated->perPage(),
                    'total' => $shiftPaginated->total(),
                ],
            ];

            return successHandler($response);
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function shiftDetailPage($id)
    {
        $shift = ShiftDetail::with(['shift'])
            ->where("shift_id", $id)->get();

        return inertia('Shift/Detail/IndexShiftDetails', [
            'shift' => $shift
        ]);
    }

    public function readShiftDetail(Request $request)
    {
        try {
            $response = ShiftDetail::with(['shift'])->where("shift_id", $request->shift_id)->get();

            return successHandler($response);
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'branch' => 'required|integer',
                'name' => 'required|string|max:255',
                'clock_in' => 'required|date_format:H:i',
                'clock_out' => 'required|date_format:H:i',
                'is_default' => 'nullable|boolean',
                'is_cross_day' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'tolerance_before_in' => 'nullable|date_format:H:i',
                'tolerance_after_in' => 'nullable|date_format:H:i',
                'tolerance_before_out' => 'nullable|date_format:H:i',
                'tolerance_after_out' => 'nullable|date_format:H:i',
            ]);

            $clockIn = Carbon::createFromFormat('H:i', $validated['clock_in']);
            $clockOut = Carbon::createFromFormat('H:i', $validated['clock_out']);

            if ($clockOut->lessThan($clockIn)) {
                if (empty($request->is_cross_day) || $request->is_cross_day == 0) {
                    throw new Exception('Jam keluar lebih kecil dari jam masuk. Centang "Absen Beda Hari" jika shift melewati tengah malam.', 422);
                }
                $clockOut->addDay();
            }

            $duration = $clockOut->diffInHours($clockIn, true);
            if ($duration < 8) {
                throw new Exception('Durasi shift harus minimal 8 jam', 422);
            }

            if (!empty($validated['tolerance_before_in'])) {
                $toleranceBeforeIn = Carbon::createFromFormat('H:i', $validated['tolerance_before_in']);
                if ($toleranceBeforeIn->gt($clockIn)) {
                    throw new Exception('Toleransi absen masuk tidak boleh melebihi jam masuk.', 422);
                }
            }

            if (!empty($validated['tolerance_after_in'])) {
                $toleranceAfterIn = Carbon::createFromFormat('H:i', $validated['tolerance_after_in']);
                if ($toleranceAfterIn->lt($clockIn)) {
                    throw new Exception('Toleransi keterlambatan tidak boleh lebih kecil dari jam masuk.', 422);
                }
            }

            if (!empty($validated['tolerance_before_out'])) {
                $toleranceBeforeOut = Carbon::createFromFormat('H:i', $validated['tolerance_before_out']);

                if (!empty($request->is_cross_day) && $request->is_cross_day == 1) {
                    $toleranceBeforeOut->addDay();
                }

                if ($toleranceBeforeOut->gt($clockOut)) {
                    throw new Exception('Toleransi absen pulang tidak boleh melebihi jam keluar.', 422);
                }
            }

            if (!empty($validated['tolerance_after_out'])) {
                $toleranceAfterOut = Carbon::createFromFormat('H:i', $validated['tolerance_after_out']);

                if (!empty($request->is_cross_day) && $request->is_cross_day == 1) {
                    $toleranceAfterOut->addDay();
                }

                if ($toleranceAfterOut->lt($clockOut)) {
                    throw new Exception('Toleransi jam pulang tidak boleh lebih kecil dari jam keluar.', 422);
                }
            }

            $exists = Shifts::where('branch', $validated['branch'])
                ->where('clock_in', $validated['clock_in'])
                ->where('clock_out', $validated['clock_out'])
                ->exists();

            if ($exists) {
                throw new Exception('Shift dengan kombinasi ini sudah ada', 422);
            }

            if (!empty($request->is_default) && $request->is_default == 1) {
                $defaultExists = Shifts::where('division', $validated['division'])
                    ->where('branch', $validated['branch'])
                    ->where('is_default', 1)
                    ->exists();

                if ($defaultExists) {
                    throw new Exception('Hanya boleh ada satu shift default untuk kombinasi divisi dan cabang ini', 422);
                }
            }

            DB::beginTransaction();

            $shift = new Shifts();
            $shift->branch = $validated['branch'];
            $shift->name = $validated['name'];
            $shift->clock_in = $validated['clock_in'];
            $shift->clock_out = $validated['clock_out'];
            $shift->is_default = $request->is_default ?? 0;
            $shift->is_cross_day = $request->is_cross_day ?? 0;
            $shift->is_active = $request->is_active ?? 1;
            $shift->tolerance_before_in = $validated['tolerance_before_in'] ?? null;
            $shift->tolerance_after_in = $validated['tolerance_after_in'] ?? null;
            $shift->tolerance_before_out = $validated['tolerance_before_out'] ?? null;
            $shift->tolerance_after_out = $validated['tolerance_after_out'] ?? null;
            $shift->save();

            DB::commit();

            return successHandler();
        } catch (Exception $err) {
            DB::rollBack();
            return errorHandler($err);
        }
    }
}
