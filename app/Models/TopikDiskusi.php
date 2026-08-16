<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopikDiskusi extends Model
{
    use HasFactory;

    protected $table = 'topik_diskusi';
    protected $primaryKey = 'id_topik_diskusi';
    
    protected $fillable = [
        'id_materi',
        'id_user',
        'judul',
        'deskripsi',
        'status',
        'dibuka_pada',
        'ditutup_pada'
    ];

    protected $casts = [
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'id_materi', 'id_materi');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function komentar()
    {
        return $this->hasMany(KomentarDiskusi::class, 'id_topik_diskusi', 'id_topik_diskusi')
                    ->whereNull('id_parent')
                    ->orderBy('created_at', 'desc');
    }

    public function allKomentar()
    {
        return $this->hasMany(KomentarDiskusi::class, 'id_topik_diskusi', 'id_topik_diskusi');
    }

    public function readStatus()
    {
        return $this->hasMany(DiskusiReadStatus::class, 'id_topik_diskusi', 'id_topik_diskusi');
    }

    public function isAktif()
    {
        return $this->status === 'aktif';
    }

    public function getUnreadCountForUser($userId)
    {
        $lastRead = $this->readStatus()->where('id_user', $userId)->first();
        
        if (!$lastRead) {
            return $this->allKomentar()->count();
        }

        return $this->allKomentar()
                    ->where('created_at', '>', $lastRead->last_read_at)
                    ->where('id_user', '!=', $userId)
                    ->count();
    }
}
