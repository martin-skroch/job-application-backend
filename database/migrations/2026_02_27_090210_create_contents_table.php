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
        Schema::create('contents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('heading');
            $table->text('text');
            $table->string('image')->nullable();
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
        Schema::dropIfExists('contents');
    }
};
