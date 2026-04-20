<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $table = 'employee_shifts';

    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'branch',
        'shift_snapshot',
        'is_holiday',
        'source',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'shift_snapshot' => 'array',
        'is_holiday' => 'boolean',
    ];

    public function shift()
    {
        return $this->belongsTo(Shifts::class, 'shift_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id'); 
    }

    public function branchRel()
    {
        return $this->belongsTo(Branch::class, 'branch');
    }
}