<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftCategory extends Model
{
    protected $table = 'shift_categories';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shifts()
    {
        return $this->hasMany(Shifts::class, 'shift_category');
    }
}