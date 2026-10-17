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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignUlid('vacancy_id')->nullable()->after('id');
        });

        // Create a vacancy for every application
        $applications = DB::table('applications')->get();

        foreach ($applications as $application) {
            $vacancyId = (string) Str::ulid();

            DB::table('vacancies')->insert([
                'id' => $vacancyId,
                'user_id' => $application->user_id,
                'title' => $application->title,
                'source' => $application->source,
                'description' => $application->description,
                'contact_name' => $application->contact_name,
                'contact_email' => $application->contact_email,
                'contact_phone' => $application->contact_phone,
                'company_name' => $application->company_name,
                'company_address' => $application->company_address,
                'company_website' => $application->company_website,
                'created_at' => $application->created_at,
                'updated_at' => $application->updated_at,
                'deleted_at' => $application->deleted_at,
            ]);

            DB::table('applications')
                ->where('id', $application->id)
                ->update(['vacancy_id' => $vacancyId]);
        }

        // Bookmarked applications become plain vacancies
        foreach ($applications as $application) {
            $latestStatus = DB::table('applications_history')
                ->where('application_id', $application->id)
                ->whereNotNull('status')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->value('status');

            if ($latestStatus !== 'bookmarked') {
                continue;
            }

            DB::table('analytics')->where('application_id', $application->id)->delete();
            DB::table('applications_history')->where('application_id', $application->id)->delete();
            DB::table('applications')->where('id', $application->id)->delete();
        }

        DB::table('applications_history')->where('status', 'bookmarked')->delete();

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['title', 'source', 'description']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignUlid('vacancy_id')->nullable(false)->change();
            $table->unique('vacancy_id');
            $table->foreign('vacancy_id')->references('id')->on('vacancies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('source')->nullable()->after('title');
            $table->text('description')->nullable()->after('source');
        });

        $vacancies = DB::table('vacancies')->get()->keyBy('id');

        foreach (DB::table('applications')->get(['id', 'vacancy_id']) as $application) {
            $vacancy = $vacancies->get($application->vacancy_id);

            if ($vacancy === null) {
                continue;
            }

            DB::table('applications')->where('id', $application->id)->update([
                'title' => $vacancy->title,
                'source' => $vacancy->source,
                'description' => $vacancy->description,
            ]);
        }

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['vacancy_id']);
            $table->dropUnique(['vacancy_id']);
            $table->dropColumn('vacancy_id');
        });
    }
};
