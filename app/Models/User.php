<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'absent_code',
        'nik',
        'name',
        'status',
        'grade',
        'dept',
        'jabatan',
        'kemandoran',
        'sex',
        'ttl',
        'no_baju',
        'gol_darah',
        'start',
        'pendidikan',
        'agama',
        'domisili',
        'no_ktp',
        'no_telpon',
        'kis',
        'kpj',
        'bank',
        'no_bank',
        'suku',
        'no_sepatu_safety',
        'latitude',
        'longitude',
        'work_hour_id',
        'start_work_user',
        'end_work_user',
        'loc',
        'sistem_absensi',
        'aktual_cuti',
        'status_pernikahan',
        'istri_suami',
        'anak_1',
        'anak_2',
        'anak_3',
        'access_by',
        'image_url',
        'role_app',
        'active',
        'inputed_by',
        'email',
        'password',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function subJobs()
    {
        return $this->hasMany(UserSubJob::class, 'nik', 'nik');
    }

}
