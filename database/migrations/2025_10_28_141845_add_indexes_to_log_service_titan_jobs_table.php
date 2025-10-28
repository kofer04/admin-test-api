<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('log_service_titan_jobs', function (Blueprint $table) {
            // Individual indexes
            $table->index('market_id', 'idx_market_id');
            $table->index('start', 'idx_start');
            $table->index('deleted_at', 'idx_deleted_at');

            // Composite index for the most common query pattern
            $table->index(['market_id', 'start', 'deleted_at'], 'idx_market_start_deleted');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->index('name', 'idx_name');
        });
    }

    public function down()
    {
        Schema::table('log_service_titan_jobs', function (Blueprint $table) {
            $table->dropIndex('idx_market_id');
            $table->dropIndex('idx_start');
            $table->dropIndex('idx_deleted_at');
            $table->dropIndex('idx_market_start_deleted');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->dropIndex('idx_name');
        });
    }
};
