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
        Schema::create('experiences', function (Blueprint $table) {
            $table->ulid('id');
            $table->foreignId('user_id')->index();
            $table->foreignId('resume_id')->index();
            $table->string('position');
            $table->string('institution')->nullable();
            $table->string('location')->nullable();
            $table->string('type')->nullable();
            $table->date('entry');
            $table->date('exit')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
