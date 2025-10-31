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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();

            // Polymorphic columns for owner
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();

            $table->timestamps();

            // Composite unique index: key must be unique per owner (or system-wide if owner is NULL)
            $table->unique(['key', 'owner_type', 'owner_id']);
            $table->index(['owner_type', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
