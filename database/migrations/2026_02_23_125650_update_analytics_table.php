<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            $table->integer('count')->nullable();
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->nullable();
        });

        $groups = DB::table('analytics')
            ->select('application_id', 'session')
            ->groupBy('application_id', 'session')
            ->get();

        foreach ($groups as $group) {
            $entries = DB::table('analytics')
                ->where('application_id', $group->application_id)
                ->where('session', $group->session)
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $oldest = $entries->first();
            $newest = $entries->last();

            DB::table('analytics')
                ->where('id', $oldest->id)
                ->update([
                    'count' => $entries->count(),
                    'updated_at' => $newest->created_at,
                ]);

            if ($entries->count() > 1) {
                DB::table('analytics')
                    ->whereIn('id', $entries->skip(1)->pluck('id')->toArray())
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics', function (Blueprint $table) {
            $table->dropColumn('count');
            $table->dropColumn('updated_at');
        });
    }
};
