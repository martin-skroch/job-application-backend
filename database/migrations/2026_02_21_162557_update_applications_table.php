<?php

use App\Enum\SalaryBehaviors;
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
        Schema::table('applications', function (Blueprint $table) {
            $table->integer('salary_behavior')->default(SalaryBehaviors::Hidden);
            $table->integer('salary_desire')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('salary_behavior');
            $table->dropColumn('salary_desire');
        });
    }
};
