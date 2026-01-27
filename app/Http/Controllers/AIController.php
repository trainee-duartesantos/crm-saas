<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\AIService;
use App\Services\ActivityLogger;
use App\Models\UserInvite;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;

class AIController extends Controller
{
    public function suggestInviteFollowUp(AIService $ai)
    {
        $message = $ai->suggest('invite_pending');

        activity_log(
            'ai.suggestion',
            null,
            [
                'message' => $message,
                'context' => 'invite_pending',
            ]
        );

        return back()->with('success', 'AI suggestion generated.');
    }

    public function summarizeTimeline(
        AIService $ai,
        ActivityLogger $logger
    ): RedirectResponse {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 1️⃣ Buscar últimos 30 dias
        $logs = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->latest()
            ->get();

        // 2️⃣ Preparar contexto para a AI
        $context = $logs->map(function ($log) {
            return "{$log->created_at}: {$log->action}";
        })->implode("\n");

        // 3️⃣ Prompt
        $summary = $ai->summarizeTimeline($context);

        // 4️⃣ Guardar na timeline
        $logger->log(
            action: 'ai.timeline.summary',
            metadata: [
                'message' => $summary,
                'period' => 'last_30_days',
            ]
        );

        return back();
    }

    public function detectRisks(AIService $ai, ActivityLogger $logger)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 🔍 Convites pendentes há mais de 7 dias
        $pendingInvites = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at') // ajusta se usares outro campo
            ->where('created_at', '<=', now()->subDays(7))
            ->count();

        if ($pendingInvites === 0) {
            return back();
        }

        // 🧠 Mensagem da AI (mock inteligente)
        $message = $ai->suggest('invite_risk', [
            'count' => $pendingInvites,
        ]);

        // 📝 Registar na timeline
        $logger->log(
            action: 'ai.risk.detected',
            metadata: [
                'type' => 'pending_invites',
                'count' => $pendingInvites,
                'message' => $message,
            ]
        );

        return back();
    }

    public function draftInviteFollowUp(
        AIService $ai,
        ActivityLogger $logger
    ) {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 🎯 Convite pendente mais antigo
        $invite = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at') // ajusta se necessário
            ->orderBy('created_at')
            ->first();

        if (! $invite) {
            return back();
        }

        // ✉️ Gerar email com AI
        $email = $ai->draftEmail(
            goal: "following up on an invitation sent to {$invite->email}"
        );

        // 📝 Guardar na timeline
        $logger->log(
            action: 'ai.follow_up.draft',
            metadata: [
                'email' => $invite->email,
                'subject' => $email['subject'],
                'body' => $email['body'],
            ]
        );

        return back();
    }

    public function generateTenantInsight(
        AIService $ai,
        ActivityLogger $logger
    ) {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 📊 Métricas reais
        $metrics = [
            'invites_total' => \App\Models\UserInvite::where('tenant_id', $tenant->id)->count(),
            'invites_accepted' => \App\Models\UserInvite::where('tenant_id', $tenant->id)
                ->whereNotNull('accepted_at')
                ->count(),
            'invites_pending' => \App\Models\UserInvite::where('tenant_id', $tenant->id)
                ->whereNull('accepted_at')
                ->count(),
            'activity_last_7_days' => ActivityLog::where('tenant_id', $tenant->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        // 🧠 Contexto para a AI
        $context = <<<TEXT
    Tenant metrics:
    - Total invitations: {$metrics['invites_total']}
    - Accepted invitations: {$metrics['invites_accepted']}
    - Pending invitations: {$metrics['invites_pending']}
    - Activities in last 7 days: {$metrics['activity_last_7_days']}
    TEXT;

        // 🤖 Insight executivo
        $message = $ai->askExecutiveInsight($context);

        // 📝 Guardar na timeline
        $logger->log(
            action: 'ai.tenant.insight',
            metadata: [
                'message' => $message,
                'metrics' => $metrics,
            ]
        );

        return back();
    }

    public function recommendNextAction(
        AIService $ai,
        ActivityLogger $logger
    ) {
        $this->authorize('viewAny', ActivityLog::class);

        $tenant = app('tenant');

        // 🔍 Métricas simples para decisão
        $invitesPending = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->count();

        $aiEvents = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', 'like', 'ai.%')
            ->count();

        // 🧠 AI decide próxima ação
        $message = $ai->generateTenantInsight([
            'invites_pending' => $invitesPending,
            'ai_events_total' => $aiEvents,
        ]);

        // 📝 Guardar na timeline
        $logger->log(
            action: 'ai.recommendation.next_action',
            metadata: [
                'message' => $message,
                'invites_pending' => $invitesPending,
                'ai_events_total' => $aiEvents,
            ]
        );

        return back();
    }

}
