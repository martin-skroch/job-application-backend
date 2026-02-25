<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        // Migrate existing notes to history entries
        $applications = DB::table('applications')
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->get(['id', 'notes', 'created_at']);

        foreach ($applications as $application) {
            DB::table('applications_history')->insert([
                'id' => (string) Str::ulid(),
                'application_id' => $application->id,
                'status' => null,
                'comment' => $application->notes,
                'created_at' => $application->created_at,
                'updated_at' => $application->created_at,
            ]);
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        // Set draft status for all applications without a status history entry
        $applications = DB::table('applications')
            ->whereNotExists(function ($query) {
                $query->from('applications_history')
                    ->whereColumn('applications_history.application_id', 'applications.id')
                    ->whereNotNull('applications_history.status');
            })
            ->get(['id', 'created_at']);

        foreach ($applications as $application) {
            DB::table('applications_history')->insert([
                'id' => (string) Str::ulid(),
                'application_id' => $application->id,
                'status' => 'draft',
                'comment' => null,
                'created_at' => $application->created_at,
                'updated_at' => $application->created_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('notes')->nullable();
        });

        Schema::dropIfExists('applications_history');
    }
};
