<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class KomentarDiskusi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'komentar_diskusi';
    protected $primaryKey = 'id_komentar';
    
    protected $fillable = [
        'id_topik_diskusi',
        'id_user',
        'id_parent',
        'konten',
        'diedit',
        'edited_at'
    ];

    protected $casts = [
        'diedit' => 'boolean',
        'edited_at' => 'datetime',
    ];

    public function topik()
    {
        return $this->belongsTo(TopikDiskusi::class, 'id_topik_diskusi', 'id_topik_diskusi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function parent()
    {
        return $this->belongsTo(KomentarDiskusi::class, 'id_parent', 'id_komentar');
    }

    public function replies()
    {
        return $this->hasMany(KomentarDiskusi::class, 'id_parent', 'id_komentar')
                    ->orderBy('created_at', 'asc');
    }

    public function likes()
    {
        return $this->hasMany(LikeKomentar::class, 'id_komentar', 'id_komentar');
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('id_user', $userId)->exists();
    }

    public function getLikesCount()
    {
        return $this->likes()->count();
    }
}
