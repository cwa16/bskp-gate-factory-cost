<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubJob extends Model
{
    protected $fillable = [
        'nik',
        'work_date',
        'sub_job',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'nik', 'nik');
    }
}
