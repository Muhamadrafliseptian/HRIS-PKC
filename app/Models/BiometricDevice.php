<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricDevice extends Model
{
    protected $table = "biometric_devices";
    protected $guarded = [];

    public function dtbranch()
    {
        return $this->belongsTo(Branch::class, 'branch');
    }

    public function category()
    {
        return $this->belongsTo(EmployeeStatus::class, 'biometric_category_id');
    }

    public function users()
    {
        return $this->hasMany(BiometricUsers::class, 'device_id');
    }
}