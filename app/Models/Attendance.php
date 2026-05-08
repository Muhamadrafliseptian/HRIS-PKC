<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendances';

    protected $guarded = [''];

    public function dtemployee()
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }

    public function dtbranch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function inDevice()
    {
        return $this->belongsTo(BiometricDevice::class, 'in_device_id', 'id');
    }

    public function outDevice()
    {
        return $this->belongsTo(BiometricDevice::class, 'out_device_id', 'id');
    }

    public function inBranch()
    {
        return $this->belongsTo(Branch::class, 'in_branch', 'id');
    }

    public function outBranch()
    {
        return $this->belongsTo(Branch::class, 'out_branch', 'id');
    }
}