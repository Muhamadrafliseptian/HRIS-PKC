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
}
