<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('log_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained('markets')->cascadeOnDelete();
            $table->foreignId('event_name_id')->constrained('event_names')->cascadeOnDelete();
            $table->string('session_id', 64); // unique per user session (using 64 chars for hash tokens)
            $table->json('data')->nullable();
            $table->timestamps(6);
            $table->softDeletes();

            // optimize funnel lookups
            $table->index(['market_id', 'event_name_id', 'session_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('log_events');
    }
};
