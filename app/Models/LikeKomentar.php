<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LikeKomentar extends Model
{
    use HasFactory;

    protected $table = 'like_komentar';
    protected $primaryKey = 'id_like';
    
    protected $fillable = [
        'id_komentar',
        'id_user'
    ];

    public function komentar()
    {
        return $this->belongsTo(KomentarDiskusi::class, 'id_komentar', 'id_komentar');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
