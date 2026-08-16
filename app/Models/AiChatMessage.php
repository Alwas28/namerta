<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatMessage extends Model
{
    protected $table = 'ai_chat_messages';
    
    protected $fillable = [
        'id_user',
        'id_modul',
        'id_materi',
        'user_message',
        'ai_response',
        'context',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class, 'id_modul', 'id_modul');
    }

    public function materi(): BelongsTo
    {
        return $this->belongsTo(Materi::class, 'id_materi', 'id_materi');
    }

    // Scopes untuk query
    public function scopeByModulMateri($query, $modulId, $materiId)
    {
        return $query->where('id_modul', $modulId)
                    ->where('id_materi', $materiId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('id_user', $userId);
    }

    public function scopeByContext($query, $context)
    {
        return $query->where('context', $context);
    }
}