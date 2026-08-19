<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCost extends Model
{
    use HasFactory;

    protected $table = 'master_costs';

    protected $fillable = [
        'year',
        'status',
        'cost_per_day'
    ];
}
