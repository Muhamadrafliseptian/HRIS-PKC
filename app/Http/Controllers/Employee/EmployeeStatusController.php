<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeStatusController extends Controller
{
    //
    public function index()
    {
        try {
            return Inertia::render("Master/Employee/Status/Index", [
                'open_key' => 'master',
                'selected_key' => 'employee-status',
            ]);
        } catch (err) {

        }
    }

    public function read()
    {
        try {
            $services = EmployeeStatus::get();

            $response = [
                'status' => $services
            ];

            return successHandler($response);
        } catch (err) {

        }
    }
}
