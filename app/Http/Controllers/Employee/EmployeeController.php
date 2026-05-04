<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeService;
use App\Models\EmployeeStatus;
use Exception;
use Inertia\Inertia;
use App\Imports\EmployeeImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeController extends Controller
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

            $device = BiometricDevice::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($device) {
                return [
                    'value' => $device->id,
                    'label' => $device->name
                ];
            });

            $status = [
                [
                    'value' => 1,
                    'label' => 'active',
                ],
                [
                    'value' => 2,
                    'label' => 'non active',
                ],
            ];

            $response = [
                'branchs' => $branchs,
                'categories' => $category,
                'status' => $status,
                'services' => $service,
                'devices' => $device,
            ];

            return Inertia::render('Employee/Index', $response);

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function read(Request $request)
    {
        try {
            $query = Employee::with([
                'biometricUser',
                'dtstatus',
                'dtbranch',
                'dtservice',
                'biometricUser.device.category'
            ]);

            if ($request->branch) {
                $query->where('branch', $request->branch);
            }

            if ($request->status) {
                $query->where('employee_status', $request->status);
            }

            if ($request->employee_services) {
                $query->where('employee_services', $request->employee_services);
            }
            
            if ($request->device_id) {
                $query->whereHas('biometricUser.device', function ($q) use ($request) {
                    $q->where('device_id', $request->device_id);
                });
            }

            if ($request->search) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('nrk', 'like', "%{$search}%");
                });
            }

            $query->orderBy('name', 'asc');

            $data = $query->get();

            return successHandler($data);

        } catch (Exception $err) {
            return errorHandler($err);
        }
    }
    
    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                throw new Exception("File wajib diisi", 422);
            }

            $file = $request->file('file');

            Excel::import(
                new EmployeeImport(
                    $request->branch,
                    $request->employee_status,
                    $request->employee_services,
                    $request->name,
                ),
                $file
            );

            return successHandler("Import berhasil");
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }

}