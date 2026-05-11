<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeServicesController extends Controller
{
    public function index()
    {
        try {
            return Inertia::render("Master/Employee/Service/Index", [
                'open_key' => 'master',
                'selected_key' => 'employee-services',
            ]);
        } catch (err) {

        }
    }

    public function read()
    {
        try {
            $services = EmployeeService::get();

            $response = [
                'services' => $services
            ];

            return successHandler($response);
        } catch (err) {

        }
    }
}
