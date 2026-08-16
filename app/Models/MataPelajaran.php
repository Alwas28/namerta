<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mata_pelajaran';
    
    protected $fillable = [
        'nama_mata_pelajaran',
        'deskripsi',
        'aktif'
    ];

    public function modul(): HasMany
    {
        return $this->hasMany(Modul::class, 'id_mata_pelajaran', 'id_mata_pelajaran');
    }
}