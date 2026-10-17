<?php

namespace Tests\Feature\Database;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class MoveVacancyDataMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Run without a wrapping transaction: SQLite ignores "PRAGMA foreign_keys" inside a transaction,
     * so rebuilding the applications table would cascade-delete its history rows.
     */
    public function beginDatabaseTransaction(): void {}

    protected function tearDown(): void
    {
        foreach (['analytics', 'applications_history', 'applications', 'vacancies'] as $table) {
            DB::table($table)->delete();
        }

        parent::tearDown();
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_10_17_124743_move_vacancy_data_out_of_applications.php');
    }

    /**
     * Insert a legacy application row (before the migration ran).
     *
     * @param  array<string, mixed>  $attributes
     */
    private function insertLegacyApplication(array $attributes = []): string
    {
        $id = (string) Str::ulid();

        DB::table('applications')->insert([
            'id' => $id,
            'user_id' => 1,
            'title' => 'Legacy Developer',
            'source' => 'https://example.com/jobs/legacy',
            'description' => 'Legacy description.',
            'company_name' => 'Legacy GmbH',
            'contact_email' => 'jobs@legacy.test',
            'created_at' => now(),
            'updated_at' => now(),
            ...$attributes,
        ]);

        return $id;
    }

    private function addStatus(string $applicationId, ?string $status, int $minutesAgo): void
    {
        DB::table('applications_history')->insert([
            'id' => (string) Str::ulid(),
            'application_id' => $applicationId,
            'status' => $status,
            'created_at' => now()->subMinutes($minutesAgo),
            'updated_at' => now()->subMinutes($minutesAgo),
        ]);
    }

    public function test_migration_moves_vacancy_data_and_turns_bookmarks_into_vacancies(): void
    {
        $this->migration()->down();

        $this->assertTrue(Schema::hasColumn('applications', 'title'));
        $this->assertFalse(Schema::hasColumn('applications', 'vacancy_id'));

        $sentId = $this->insertLegacyApplication(['title' => 'Sent Developer']);
        $this->addStatus($sentId, 'bookmarked', 10);
        $this->addStatus($sentId, 'sent', 5);

        $bookmarkedId = $this->insertLegacyApplication(['title' => 'Bookmarked Developer']);
        $this->addStatus($bookmarkedId, 'sent', 10);
        $this->addStatus($bookmarkedId, 'bookmarked', 5);
        DB::table('analytics')->insert([
            'application_id' => $bookmarkedId,
            'session' => 'session',
            'method' => 'GET',
            'path' => '/',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $draftId = $this->insertLegacyApplication(['title' => 'Draft Developer', 'deleted_at' => now()]);

        $this->migration()->up();

        $this->assertFalse(Schema::hasColumn('applications', 'title'));
        $this->assertDatabaseCount('vacancies', 3);

        $sent = DB::table('applications')->where('id', $sentId)->first();
        $this->assertNotNull($sent->vacancy_id);
        $this->assertDatabaseHas('vacancies', [
            'id' => $sent->vacancy_id,
            'title' => 'Sent Developer',
            'source' => 'https://example.com/jobs/legacy',
            'description' => 'Legacy description.',
            'company_name' => 'Legacy GmbH',
        ]);
        $this->assertDatabaseMissing('applications_history', ['application_id' => $sentId, 'status' => 'bookmarked']);
        $this->assertDatabaseHas('applications_history', ['application_id' => $sentId, 'status' => 'sent']);

        $this->assertDatabaseMissing('applications', ['id' => $bookmarkedId]);
        $this->assertDatabaseMissing('applications_history', ['application_id' => $bookmarkedId]);
        $this->assertDatabaseMissing('analytics', ['application_id' => $bookmarkedId]);
        $this->assertDatabaseHas('vacancies', ['title' => 'Bookmarked Developer', 'deleted_at' => null]);

        $draft = DB::table('applications')->where('id', $draftId)->first();
        $this->assertNotNull($draft);
        $this->assertNotNull(DB::table('vacancies')->where('id', $draft->vacancy_id)->value('deleted_at'));
    }

    public function test_migration_can_be_rolled_back(): void
    {
        $this->migration()->down();
        $applicationId = $this->insertLegacyApplication();
        $this->migration()->up();

        $this->migration()->down();

        $this->assertDatabaseHas('applications', [
            'id' => $applicationId,
            'title' => 'Legacy Developer',
            'description' => 'Legacy description.',
        ]);
        $this->assertFalse(Schema::hasColumn('applications', 'vacancy_id'));

        $this->migration()->up();
    }
}
