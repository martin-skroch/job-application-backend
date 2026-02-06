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
        Schema::create('impressions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('image');
            $table->string('title')->nullable();
            $table->tinyText('description')->nullable();
            $table->smallInteger('order');
            $table->boolean('active')->default(false);
            $table->foreignId('user_id')->index();
            $table->foreignUlid('profile_id')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impressions');
    }
};
