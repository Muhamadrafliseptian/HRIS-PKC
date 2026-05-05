<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    //
    use SoftDeletes;

    public function childs()
    {
        return $this->hasMany(Menu::class, 'parent', 'id')->where('is_active', 1)->orderBy('ordering', 'asc');
    }
}
