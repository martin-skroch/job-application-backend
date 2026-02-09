<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('analytics', function (Blueprint $table) {
            $table->id();
            $table->ulid('application_id')->index();
            $table->string('session', 64)->index();
            $table->string('method', 10);
            $table->string('path')->index();
            $table->json('query')->nullable();
            $table->json('headers')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('analytics');
    }
};
