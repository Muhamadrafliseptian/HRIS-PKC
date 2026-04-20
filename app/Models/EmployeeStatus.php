<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeStatus extends Model
{
    protected $table = 'employee_status';

    protected $guarded = [''];

    public function devices()
    {
        return $this->hasMany(BiometricDevice::class, 'biometric_category_id');
    }
}
