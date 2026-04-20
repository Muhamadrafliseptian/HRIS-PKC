<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shifts extends Model
{
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function details()
    {
        return $this->hasMany(ShiftDetail::class, 'shift_id');
    }

    public function category()
    {
        return $this->belongsTo(ShiftCategory::class, 'shift_category');
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class, 'shift_id');
    }
}
