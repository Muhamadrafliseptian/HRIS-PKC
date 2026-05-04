<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = "employees";
    protected $guarded = [];

    public function biometricUser()
    {
        return $this->hasMany(BiometricUsers::class, 'user_id', 'user_id');
    }

    public function dtbranch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function dtstatus()
    {
        return $this->belongsTo(EmployeeStatus::class, 'employee_status', 'id');
    }

    public function dtservice()
    {
        return $this->belongsTo(EmployeeService::class, 'employee_services', 'id');
    }
}