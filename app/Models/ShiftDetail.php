<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftDetail extends Model
{
    protected $table = 'shift_details';

    protected $fillable = [
        'shift_id',
        'clock_in',
        'clock_out',
        'is_cross_day',
        'order',
        'tolerance_before_in',
        'tolerance_after_in',
        'tolerance_before_out',
        'tolerance_after_out',
        'min_work_minutes',
        'segment_type',
    ];

    protected $casts = [
        'is_cross_day' => 'boolean',
    ];

    public function shift()
    {
        return $this->belongsTo(Shifts::class, 'shift_id');
    }
}