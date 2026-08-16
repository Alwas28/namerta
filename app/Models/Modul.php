<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modul extends Model
{
    protected $table = 'modul';
    protected $primaryKey = 'id_modul';
    
    protected $fillable = [
        'nama_modul',
        'desk',
        'pengalaman_belajar',
        'koneksi_materi',
        'tes_kompetensi',
        'aksi_nyata',
        'id_mata_pelajaran'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran', 'id_mata_pelajaran');
    }

    public function materi(): HasMany
    {
        return $this->hasMany(Materi::class, 'id_modul', 'id_modul');
    }

    public function aiChatMessages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'id_modul', 'id_modul');
    }
}