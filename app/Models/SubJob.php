<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubJob extends Model
{
     protected $fillable = [
        'code',
        'area',
        'name',
        'color',
    ];
}
