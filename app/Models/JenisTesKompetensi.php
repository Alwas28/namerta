<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisTesKompetensi extends Model
{
    use HasFactory;

    protected $table = 'jenis_tes_kompetensi';
    protected $primaryKey = 'id_jenis_tes_kompetensi';
    public $timestamps = true;

    protected $fillable = [
        'id_modul',
        'essay'
    ];

    protected $attributes = [
        'essay' => 'N'
    ];

    // Relationship dengan Modul
    public function modul()
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }
}
