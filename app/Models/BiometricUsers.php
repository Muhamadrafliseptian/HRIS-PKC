<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricUsers extends Model
{
    use SoftDeletes;
    protected $table = 'biometric_users';
    protected $guarded = [''];
    
    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }
}
