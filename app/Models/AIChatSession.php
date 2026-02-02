<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIChatSession extends Model
{
    protected $table = 'ai_chat_sessions';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'last_message_at',
    ];

    public function messages()
    {
        return $this->hasMany(AIChatMessage::class, 'session_id');
    }
}
