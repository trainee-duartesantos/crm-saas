<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();

            $table->string('file_path');
            $table->string('original_name');

            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['tenant_id', 'deal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
