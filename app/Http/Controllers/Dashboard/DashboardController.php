<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Shifts;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $totalEmployee = Employee::count();
            $totalBranch = Branch::count();
            $totalShift = Shifts::count();
            $totalDevice = BiometricDevice::count();

            $deviceOnline = BiometricDevice::where('status', 'online')->count();

            return Inertia::render('Dashboard/Index', [
                'stats' => [
                    'employees' => $totalEmployee,
                    'branches' => $totalBranch,
                    'shifts' => $totalShift,
                    'devices' => $deviceOnline,
                    'total_devices' => $totalDevice,
                ]
            ]);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
