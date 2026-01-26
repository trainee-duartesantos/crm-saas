<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();

            // Ex.: invites.sent, invites.accepted, users.created, etc.
            $table->string('action', 100);

            // O “alvo” do log (Invite, User, Deal, etc.)
            $table->nullableMorphs('subject'); // subject_type + subject_id

            // Info extra (email do convidado, payload, before/after, ip, user_agent…)
            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'action']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
