<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLogs extends Model
{
    protected $table = 'attendance_logs';
    protected $guarded = [''];

    public function dtbiouser()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function dtbranch()
    {
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function devices()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id', 'id');
    }
}
