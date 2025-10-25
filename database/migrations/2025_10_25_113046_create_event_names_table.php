<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('event_names', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('display_on_client')->default(false);
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('event_names');
    }
};
