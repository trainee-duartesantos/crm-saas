<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIChatMessage extends Model
{
    protected $table = 'ai_chat_messages';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'session_id',
        'role',
        'content',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
