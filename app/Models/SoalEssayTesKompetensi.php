<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalEssayTesKompetensi extends Model
{
    use HasFactory;

    protected $table = 'soal_essay_tes_kompetensi';
    protected $primaryKey = 'id_soal_essay';
    public $timestamps = true;

    protected $fillable = [
        'id_modul',
        'soal'
    ];

    // Relationship dengan Modul
    public function modul()
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }

    // Relationship dengan Jawaban Essay
    public function jawaban()
    {
        return $this->hasMany(JawabanEssayTesKompetensi::class, 'id_soal_essay', 'id_soal_essay');
    }
}