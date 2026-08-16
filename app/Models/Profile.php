<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profile';
    protected $primaryKey = 'id_profile';
    
    protected $fillable = [
        'nama',
        'id_user',
        'id_sekolah',
        'alamat',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status',
        'id_status'
    ];

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relasi dengan user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi dengan sekolah
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'id_sekolah', 'id_sekolah');
    }
}