<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('path')->nullable();
            $table->timestamps(6);
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('markets');
    }
};
