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
        Schema::table('log_events', function (Blueprint $table) {
            // Composite index for conversion funnel queries
            // Optimizes WHERE created_at BETWEEN + event_name_id IN + market_id filtering
            $table->index(['created_at', 'event_name_id', 'market_id'], 'idx_log_events_conversion_funnel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_events', function (Blueprint $table) {
            $table->dropIndex('idx_log_events_conversion_funnel');
        });
    }
};
