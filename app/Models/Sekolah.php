<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    protected $table = 'sekolah';
    protected $primaryKey = 'id_sekolah';
    
    protected $fillable = [
        'kode_sekolah',
        'nama_sekolah',
        'alamat',
        'kelurahan',
        'kota',
        'provinsi',
        'profile',
        'logo'
    ];

    public $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    // Relasi dengan profile
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'id_sekolah', 'id_sekolah');
    }
}