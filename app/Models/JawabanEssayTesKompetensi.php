<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawabanEssayTesKompetensi extends Model
{
    use HasFactory;

    protected $table = 'jawaban_essay_tes_kompetensi';
    protected $primaryKey = 'id_jawaban_essay';
    public $timestamps = true;

    protected $fillable = [
        'id_soal_essay',
        'id_user',
        'jawaban'
    ];

    // Relationship dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relationship dengan Soal Essay
    public function soal()
    {
        return $this->belongsTo(SoalEssayTesKompetensi::class, 'id_soal_essay', 'id_soal_essay');
    }

    // Relationship dengan Profile untuk nama siswa
    public function profile()
    {
        return $this->hasOneThrough(Profile::class, User::class, 'id_user', 'id_user', 'id_user', 'id_user');
    }

    // Accessor untuk excerpt jawaban
    public function getExcerptAttribute($limit = 100)
    {
        return strlen($this->jawaban) > $limit 
            ? substr($this->jawaban, 0, $limit) . '...'
            : $this->jawaban;
    }

    // Accessor untuk word count
    public function getWordCountAttribute()
    {
        return str_word_count(strip_tags($this->jawaban));
    }
}