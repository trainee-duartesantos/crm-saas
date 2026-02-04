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
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');
        $user = $request->user();

        if (!$tenant) {
            abort(400, 'Tenant não definido');
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'page' => ['nullable', 'string', 'max:80'],
            'session_id' => ['nullable', 'integer', 'exists:ai_chat_sessions,id'],
        ]);

        // Sessão
        $session = !empty($data['session_id'])
            ? AIChatSession::where('id', $data['session_id'])
                ->where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->firstOrFail()
            : AIChatSession::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'title' => $data['page'] ? "Chat - {$data['page']}" : 'Chat',
                'last_message_at' => now(),
            ]);

        AIChatMessage::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $data['message'],
        ]);

        // 🔍 Interpretar intenção
        $intent = $engine->interpret($data['message'], $tenant, $user);

        // ⚙️ Executar ação real
        $payload = $runner->run($intent, [
            'tenant_id' => $tenant->id,
            'tenant' => $tenant,
            'user' => $user,
            'page' => $data['page'] ?? null,
        ]);


        AIChatMessage::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'session_id' => $session->id,
            'role' => 'assistant',
            'content' => $payload['answer'] ?? '(sem resposta)',
            'intent' => $intent,
            'payload' => $payload,
        ]);

        $session->update(['last_message_at' => now()]);

        activity_log('ai.chat.query', null, [
            'page' => $data['page'] ?? null,
            'intent' => $intent['intent'] ?? null,
        ]);

        return response()->json([
            'session_id' => $session->id,
            'payload' => $payload,
            'clarifying_question' => $intent['clarifying_question'] ?? null,
        ]);
    }

}
