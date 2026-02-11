<?php

use App\Models\Analytics;
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
        Analytics::each(function(Analytics $entry) {
            $data = $entry->ip . '|' . $entry->user_agent;
            $session = hash_hmac('sha256', $data, config('app.key'));

            $entry->update([
                'session' => $session,
            ]);
        });

        Schema::table('analytics', function (Blueprint $table) {
            $table->dropColumn('ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            $table->string('ip', 45)->nullable();
        });
    }
};
