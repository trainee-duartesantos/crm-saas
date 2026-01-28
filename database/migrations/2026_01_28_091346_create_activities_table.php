<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('person_id')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('deals')->nullOnDelete();

            $table->string('type'); // call | meeting | email | task
            $table->string('title');
            $table->text('notes')->nullable();

            $table->dateTime('due_at')->nullable();       // “calendário”
            $table->dateTime('completed_at')->nullable(); // concluída

            $table->timestamps();

            $table->index(['tenant_id', 'due_at']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
