<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricUsers extends Model
{
    protected $table = 'biometric_users';
    protected $guarded = [''];
    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }
}
