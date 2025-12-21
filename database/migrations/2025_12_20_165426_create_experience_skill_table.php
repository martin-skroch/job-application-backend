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
        Schema::create('experience_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('experience_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('skill_id')->constrained()->cascadeOnDelete();
            $table->unique(['experience_id', 'skill_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_skill');
    }
};
