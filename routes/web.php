<?php

use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Biometric\BiometricController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Devices\DevicesController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Report\AttendanceReportController;
use App\Http\Controllers\Setting\UserController;
use App\Http\Controllers\Shift\AssignmentShiftController;
use App\Http\Controllers\Shift\ShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:120,1', 'maintenance'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login_page');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::middleware(['auth', 'is_active'])->group(function () {
        Route::get('get-menu', [MenuController::class, 'getAllMenu']);
        Route::get("/", [DashboardController::class, "index"])->name('dashboard');
        Route::prefix("attendance")->middleware('permission:attendance')->group(function () {
            Route::post('pull', [AttendanceController::class, 'pull']);
            Route::post('read', [AttendanceController::class, 'read']);
            Route::post('change-status', [AttendanceController::class, 'changeStatusAttendance']);
            Route::get('/', [AttendanceController::class, 'index']);
            Route::get('/pull/status', [AttendanceController::class, 'pullStatus']);
        });
        Route::prefix('biometric')->group(function () {
            Route::prefix("devices")->middleware('permission:biometric-devices')->group(function () {
                Route::post('read', [DevicesController::class, 'readDevices']);
                Route::post('create', [DevicesController::class, 'create']);
                Route::post('{id}/check', [DevicesController::class, 'checkDevices']);
                Route::get('/', [DevicesController::class, 'index']);
            });
            Route::prefix('users')->middleware('permission:biometric-users')->group(function () {
                Route::get("/", [BiometricController::class, 'index']);
                Route::post("sync", [BiometricController::class, 'sync']);
                Route::post("read", [BiometricController::class, 'read']);
                Route::post("create", [BiometricController::class, 'create']);
                Route::post("destroy", [BiometricController::class, 'destroy']);
                Route::post("transfer", [BiometricController::class, 'transferUser']);
            });
        });
        Route::prefix("master")->group(function () {
            Route::prefix('branch')->middleware('permission:branch')->group(function () {
                Route::get("/", [BranchController::class, 'index']);
                Route::post("read", [BranchController::class, 'read']);
            });
        });
        Route::prefix("employee")->middleware('permission:employee')->group(function () {
            Route::get("/", [EmployeeController::class, 'index']);
            Route::post("read", [EmployeeController::class, 'read']);
            Route::post("import", [EmployeeController::class, 'import']);
        });
        Route::prefix("manage")->group(function () {
            Route::prefix("shift")->group(function () {
                Route::prefix("master")->middleware('permission:manage-shift-master')->group(function () {
                    Route::get("/", [ShiftController::class, 'index']);
                    Route::post("create", [ShiftController::class, 'create']);
                    Route::post("read", [ShiftController::class, 'read']);
                });
                Route::prefix("detail")->middleware('permission:manage-shift-master')->group(function () {
                    Route::get('/{id}', [ShiftController::class, "shiftDetailPage"]);
                    Route::post("/read", [ShiftController::class, "readShiftDetails"]);
                });
                Route::prefix("assignment")->middleware('permission:manage-shift-assign')->group(function () {
                    Route::get('/', [AssignmentShiftController::class, "index"]);
                    Route::post("/read", [AssignmentShiftController::class, "read"]);
                    Route::post("/create", [AssignmentShiftController::class, "create"]);
                });
            });
        });
        Route::prefix("report")->group(function () {
            Route::prefix("attendance")->middleware('permission:report-attendance')->group(function () {
                Route::prefix("transaction")->group(function () {
                    Route::get('/', [AttendanceReportController::class, "indexTransaction"]);
                    Route::get('preview', [AttendanceReportController::class, "preview"]);
                });
                Route::prefix("log")->middleware('permission:report-attendance')->group(function () {
                    Route::get('/', [AttendanceReportController::class, "indexLog"]);
                    Route::get('preview', [AttendanceReportController::class, "preview"]);
                    Route::post('download', [AttendanceReportController::class, "download"]);
                });
            });
        });
        Route::prefix('setting')->group(function () {
            Route::prefix('users')->middleware('permission:setting-users')->group(function () {
                Route::get('/', [UserController::class, 'index']);
                Route::post('get-users', [UserController::class, 'getUsers']);
                Route::post('change-permission', [UserController::class, 'changePermission']);
            });
        });
    });
});