<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Services\AIService;
use App\Services\ActivityLogger;
use App\Models\UserInvite;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use App\Mail\FollowUpMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Deal;
use App\Services\AI\GenerateDealSummary;
use App\Services\AI\DealNextBestActionService;

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
        abort_unless(auth()->user()->isOwner(), 403);

        $tenant = app('tenant');

        $contextPrefix = "You are advising the OWNER of a SaaS CRM. Focus on revenue, growth, adoption, and risks.\n\n";

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
        $message = $ai->askExecutiveInsight($contextPrefix . $context);

        $logger->log(
            action: 'ai.tenant.insight',
            metadata: [
                'message' => $message,
                'confidence' => 0.85,
            ]
        );

        return back();
    }

    public function recommendNextAction(
        AIService $ai,
        ActivityLogger $logger
    ) {
        abort_unless(auth()->user()->isOwner(), 403);

        $tenant = app('tenant');

        $contextPrefix = "You are advising the OWNER of a SaaS CRM. Focus on revenue, growth, adoption, and risks.\n\n";

        // 🔍 Métricas simples para decisão
        $invitesPending = UserInvite::query()
            ->where('tenant_id', $tenant->id)
            ->whereNull('accepted_at')
            ->count();

        $aiEvents = ActivityLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', 'like', 'ai.%')
            ->count();

        $context = <<<TEXT
        Tenant metrics:
        - Pending invitations: {$invitesPending}
        - Total AI events so far: {$aiEvents}
        TEXT;
        // 🧠 AI decide próxima ação
        $message = $ai->askExecutiveInsight($contextPrefix . $context);

        $logger->log(
            action: 'ai.tenant.next_action',
            metadata: [
                'message' => $message,
                'confidence' => 0.8,
            ]
        );

        return back();
    }

    public function detectActivityFollowUps(
        AIService $ai,
        ActivityLogger $logger
    ) {
        $this->authorize('viewAny', ActivityLog::class);

        $tenantId = app('tenant')->id;

        $overdueActivities = Activity::query()
            ->where('tenant_id', $tenantId)
            ->whereNull('completed_at')
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->subDay())
            ->with(['person', 'deal'])
            ->limit(5)
            ->get();

        if ($overdueActivities->isEmpty()) {
            return back();
        }

        foreach ($overdueActivities as $activity) {
            $suggestion = $ai->suggest('activity_follow_up', [
                'title' => $activity->title,
                'type' => $activity->type,
            ]);

            $logger->log(
                action: 'ai.follow_up.suggested',
                metadata: [
                    'activity_id' => $activity->id,
                    'activity_title' => $activity->title,
                    'suggestion' => $suggestion,
                ]
            );
        }

        return back();
    }

    public function sendFollowUp(
        Activity $activity,
        AIService $ai,
        ActivityLogger $logger
    ) {
        $this->authorize('viewAny', ActivityLog::class);

        if (! $activity->person?->email) {
            return back();
        }

        $email = $ai->draftEmail(
            goal: "following up on '{$activity->title}'"
        );

        Mail::to($activity->person->email)
            ->send(new FollowUpMail(
                $email['subject'],
                $email['body']
            ));

        $logger->log(
            action: 'email.follow_up.sent',
            metadata: [
                'activity_id' => $activity->id,
                'to' => $activity->person->email,
                'subject' => $email['subject'],
            ]
        );

        return back();
    }

    public function dealNextAction(Deal $deal, DealNextBestActionService $service)
    {
        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $item = $service->forDeal($deal);

        return response()->json([
            'item' => $item,
        ]);
    }

    public function summarizeDeal(Deal $deal)
    {
        $this->authorize('viewAny', ActivityLog::class);
        abort_if($deal->tenant_id !== app('tenant')->id, 403);

        $summary = app(GenerateDealSummary::class)->generate($deal);

        $deal->update([
            'ai_summary' => [
                'text' => $summary['description'],
                'confidence' => $summary['meta']['confidence'],
                'generated_at' => now(),
            ],
        ]);

        activity_log('ai.deal.summary.generated', $deal, [
            'message' => $summary['description'],
            'confidence' => $summary['meta']['confidence'],
        ]);

        return back();

    }

}
