<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanTesKompetensi extends Model
{
    use HasFactory;

    protected $table = 'jawaban_tes_kompetensi';
    protected $primaryKey = 'id_jawaban_tes_kompetensi';
    public $timestamps = true;

    protected $fillable = [
        'id_user',
        'id_soal_tes_kompetensi',
        'jawaban',
        'benar'
    ];

    protected $attributes = [
        'benar' => null
    ];

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relationship dengan Soal
    public function soal()
    {
        return $this->belongsTo(SoalTesKompetensi::class, 'id_soal_tes_kompetensi', 'id_soal_tes_kompetensi');
    }

    // Relationship dengan Profile untuk nama siswa
    public function profile()
    {
        return $this->hasOneThrough(Profile::class, User::class, 'id_user', 'id_user', 'id_user', 'id_user');
    }

    // Accessor untuk status jawaban
    public function getStatusAttribute()
    {
        if ($this->benar === null) {
            return 'belum_dinilai';
        }
        return $this->benar === 'Y' ? 'benar' : 'salah';
    }

    // Method untuk mengecek apakah jawaban benar
    public function isCorrect()
    {
        return $this->benar === 'Y';
    }

    // Method untuk mengecek apakah sudah dinilai
    public function isGraded()
    {
        return $this->benar !== null;
    }

    // Scope untuk filter jawaban yang sudah dinilai
    public function scopeGraded($query)
    {
        return $query->whereNotNull('benar');
    }

    // Scope untuk filter jawaban yang belum dinilai
    public function scopeUngraded($query)
    {
        return $query->whereNull('benar');
    }

    // Scope untuk filter jawaban benar
    public function scopeCorrect($query)
    {
        return $query->where('benar', 'Y');
    }

    // Scope untuk filter jawaban salah
    public function scopeIncorrect($query)
    {
        return $query->where('benar', 'N');
    }
}