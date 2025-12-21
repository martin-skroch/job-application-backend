<?php

use App\Enum\SalaryPeriod;
use App\Enum\Workplace;
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
        Schema::create('vacancies', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->index();
            $table->string('title');
            $table->string('source')->nullable();
            $table->text('content')->nullable();
            $table->enum('salary_period', SalaryPeriod::values())->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->json('workplace')->nullable();
            $table->integer('weekhours')->nullable();
            $table->string('location')->nullable();
            $table->string('company')->nullable();
            $table->tinyText('address')->nullable();
            $table->string('contact')->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};
