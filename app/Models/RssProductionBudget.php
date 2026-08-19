<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RssProductionBudget extends Model
{
    use HasFactory;
    protected $fillable = ['month', 'year', 'target_qty'];
}
