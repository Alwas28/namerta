<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materi extends Model
{
    protected $table = 'materi';
    protected $primaryKey = 'id_materi';
    
    protected $fillable = [
        'id_modul',
        'nama_materi',
        'mulai_dari_diri',
        'eksplorasi_konsep',
        'ruang_kolaborasi',
        'refleksi_terbimbing',
        'demonstrasi_konseptual',
        'elaborasi_pemahaman'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }

    public function aiChatMessages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'materi_id', 'id_materi');
    }

    // Helper methods untuk komponen pembelajaran
    public function getKomponenPembelajaran(): array
    {
        return [
            'mulai_dari_diri' => $this->mulai_dari_diri,
            'eksplorasi_konsep' => $this->eksplorasi_konsep,
            'ruang_kolaborasi' => $this->ruang_kolaborasi,
            'refleksi_terbimbing' => $this->refleksi_terbimbing,
            'demonstrasi_konseptual' => $this->demonstrasi_konseptual,
            'elaborasi_pemahaman' => $this->elaborasi_pemahaman,
        ];
    }

    public function getKomponenTersedia(): array
    {
        $komponen = $this->getKomponenPembelajaran();
        return array_filter($komponen, function($value) {
            return !empty($value);
        });
    }
}