<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class JawabanKoneksiMateri extends Model
{
    use HasFactory;
    protected $table = 'jawaban_koneksi_materi';
    protected $primaryKey = 'id_jawaban_koneksi_materi';
    protected $dates = ['created_at', 'updated_at'];
}
