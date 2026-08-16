<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalTesKompetensi extends Model
{
    use HasFactory;

    protected $table = 'soal_tes_kompetensi';
    protected $primaryKey = 'id_soal_tes_kompetensi';
    public $timestamps = true;

    protected $fillable = [
        'id_modul',
        'soal',
        'a',
        'b',
        'c',
        'd',
        'jawaban_benar'
    ];

    // Relationship dengan Modul
    public function modul()
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }

    // Relationship dengan Jawaban Tes Kompetensi
    public function jawaban()
    {
        return $this->hasMany(JawabanTesKompetensi::class, 'id_soal_tes_kompetensi', 'id_soal_tes_kompetensi');
    }

    // Accessor untuk mendapatkan pilihan jawaban dalam array
    public function getPilihanAttribute()
    {
        return [
            'a' => $this->a,
            'b' => $this->b,
            'c' => $this->c,
            'd' => $this->d
        ];
    }

    // Method untuk mengecek jawaban benar
    public function isCorrectAnswer($answer)
    {
        return $this->jawaban_benar === $answer;
    }
}