<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiskusiReadStatus extends Model
{
    use HasFactory;

    protected $table = 'diskusi_read_status';
    
    protected $fillable = [
        'id_topik_diskusi',
        'id_user',
        'last_read_at'
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function topik()
    {
        return $this->belongsTo(TopikDiskusi::class, 'id_topik_diskusi', 'id_topik_diskusi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}