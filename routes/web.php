<?php

use App\Http\Controllers\Attendance\AttendanceController;
use App\Http\Controllers\Biometric\BiometricController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Devices\DevicesController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Shift\AssignmentShiftController;
use App\Http\Controllers\Shift\ShiftController;
use Illuminate\Support\Facades\Route;

Route::get("/", [DashboardController::class, "index"]);
Route::prefix("attendance")->group(function () {
    Route::post('pull', [AttendanceController::class, 'pull']);
    Route::post('read', [AttendanceController::class, 'read']);
    Route::post('change-status', [AttendanceController::class, 'changeStatusAttendance']);
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/pull/status', [AttendanceController::class, 'pullStatus']);
});
Route::prefix('biometric')->group(function () {
    Route::prefix("devices")->group(function () {
        Route::post('read', [DevicesController::class, 'readDevices']);
        Route::post('create', [DevicesController::class, 'create']);
        Route::post('{id}/check', [DevicesController::class, 'checkDevices']);
        Route::get('/', [DevicesController::class, 'index']);
    });
    Route::prefix('users')->group(function () {
        Route::get("/", [BiometricController::class, 'index']);
        Route::post("sync", [BiometricController::class, 'sync']);
        Route::post("read", [BiometricController::class, 'read']);
        Route::post("create", [BiometricController::class, 'create']);
        Route::post("destroy", [BiometricController::class, 'destroy']);
        Route::post("transfer", [BiometricController::class, 'transferUser']);
    });
});
Route::prefix("master")->group(function () {
    Route::prefix('branch')->group(function () {
        Route::get("/", [BranchController::class, 'index']);
        Route::post("read", [BranchController::class, 'read']);
    });
});
Route::prefix("employee")->group(function () {
    Route::get("/", [EmployeeController::class, 'index']);
    Route::post("read", [EmployeeController::class, 'read']);
    Route::post("import", [EmployeeController::class, 'import']);
});
Route::prefix("manage")->group(function () {
    Route::prefix("shift")->group(function () {
        Route::prefix("master")->group(function(){
            Route::get("/", [ShiftController::class, 'index']);
            Route::post("create", [ShiftController::class, 'create']);
            Route::post("read", [ShiftController::class, 'read']);
        });
        Route::prefix("detail")->group(function () {
            Route::get('/{id}', [ShiftController::class, "shiftDetailPage"]);
            Route::post("/read", [ShiftController::class, "readShiftDetails"]);
        });
        Route::prefix("assignment")->group(function () {
            Route::get('/', [AssignmentShiftController::class, "index"]);
            Route::post("/read", [AssignmentShiftController::class, "read"]);
            Route::post("/create", [AssignmentShiftController::class, "create"]);
        });
    });
});