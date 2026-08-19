<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCostPerKg extends Model
{
    use HasFactory;
    protected $fillable = ['work_date', 'area', 'budget_cpk'];
}
