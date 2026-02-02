<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\AIChatMessage;
use App\Models\AIChatSession;
use App\Services\AI\ChatEngine;
use App\Services\AI\ChatRunner;
use Illuminate\Http\Request;

class AIChatController extends Controller
{
    public function chat(Request $request, ChatEngine $engine, ChatRunner $runner)
    {
        // Reutilizamos a policy “central” (igual ao resto do projeto)
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');
        $user = $request->user();

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'page' => ['nullable', 'string', 'max:80'],
            'session_id' => ['nullable', 'integer', 'exists:ai_chat_sessions,id'],
        ]);

        // ✅ session (por utilizador)
        $session = null;

        if (!empty($data['session_id'])) {
            $session = AIChatSession::query()
                ->where('id', $data['session_id'])
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $session = AIChatSession::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'title' => $data['page'] ? ("Chat - " . $data['page']) : 'Chat',
                'last_message_at' => now(),
            ]);
        }

        // ✅ Guardar mensagem do utilizador
        AIChatMessage::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $data['message'],
        ]);

        // ✅ Contexto mínimo (sem dados sensíveis)
        $context = [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'page' => $data['page'] ?? null,
            'role' => $user->role ?? 'user',
        ];

        // 1) Interpretar intenção (NÃO responder ainda)
        $intent = $engine->interpret($data['message'], $context);

        // 2) Executar query real com tenant+perms e devolver UI payload
        $payload = $runner->run($intent, $context);

        // ✅ Guardar resposta do assistant
        AIChatMessage::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $payload['answer'] ?? '(no answer)',
            'intent' => $intent,
            'payload' => $payload,
        ]);

        $session->update(['last_message_at' => now()]);

        // ✅ Log para timeline (opcional, mas útil no PDF)
        activity_log('ai.chat.query', null, [
            'page' => $context['page'],
            'intent' => $intent['intent'] ?? null,
        ]);

        return response()->json([
            'session_id' => $session->id,
            'intent' => $intent,
            'payload' => $payload,
        ]);
    }
}
