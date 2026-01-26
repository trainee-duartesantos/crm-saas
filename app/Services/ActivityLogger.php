<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public function log(
        string $action,
        ?Model $subject = null,
        array $metadata = [],
        ?int $actorId = null,
        ?int $tenantId = null
    ): ActivityLog {
        $actorId ??= Auth::id();
        $tenantId ??= $this->resolveTenantId();

        // Proteção mínima: nunca criar logs sem tenant
        if (!$tenantId) {
            throw new \RuntimeException('Tenant não resolvido ao criar activity log.');
        }

        return ActivityLog::create([
            'tenant_id'    => $tenantId,
            'actor_id'     => $actorId,
            'action'       => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id'   => $subject?->getKey(),
            'metadata'     => $metadata ?: null,
        ]);
    }

    private function resolveTenantId(): ?int
    {
        // Ajusta ao teu projeto: o tenant pode estar no user,
        // ou num TenantManager, ou em app('tenant')…
        $user = Auth::user();
        return $user?->tenant_id ?? null;
    }
}
