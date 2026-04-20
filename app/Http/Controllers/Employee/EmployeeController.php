<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeStatus;
use Exception;
use Inertia\Inertia;
use App\Imports\EmployeeImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeController extends Controller
{
    protected $ip = "10.31.164.173";
    protected $port = 4370;

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
                'status' => $status
            ];

            return Inertia::render('Employee/Index', $response);

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function read()
    {
        try {
            $data = Employee::with(['biometricUser', 'dtstatus','dtbranch','biometricUser.device.category'])->orderBy("id", 'desc')->get();

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