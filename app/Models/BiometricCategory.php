<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricCategory extends Model
{
    public function devices()
    {
        return $this->hasMany(BiometricDevice::class, 'biometric_category_id');
    }
}
