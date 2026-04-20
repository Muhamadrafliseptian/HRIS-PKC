<?php

namespace App\Models;

use App\Models\BranchConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Village;

class Branch extends Model
{
    use SoftDeletes;

    protected $table = 'branchs';

    public function dtconfig()
    {
        return $this->belongsTo(BranchConfig::class, 'id', 'branch');
    }

    public function dtprovince()
    {
        return $this->belongsTo(Province::class, 'province', 'code')->select('name', 'code');
    }

    public function dtcity()
    {
        return $this->belongsTo(City::class, 'city', 'code')->select('name', 'code');
    }

    public function dtdistrict()
    {
        return $this->belongsTo(District::class, 'district', 'code')->select('name', 'code');
    }

    public function dtvillage()
    {
        return $this->belongsTo(Village::class, 'village', 'code')->select('name', 'code');
    }

    public function devices()
    {
        return $this->hasMany(BiometricDevice::class, 'branch');
    }
}
