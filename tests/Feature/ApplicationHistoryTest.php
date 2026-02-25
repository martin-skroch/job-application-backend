<?php

namespace Tests\Feature;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_has_many_history_entries(): void
    {
        $application = Application::factory()->create();
        ApplicationHistory::factory()->count(3)->create(['application_id' => $application->id]);

        $this->assertCount(3, $application->history);
    }

    public function test_history_entry_with_status_change_and_comment(): void
    {
        $application = Application::factory()->create();

        $history = $application->history()->create([
            'status' => ApplicationStatus::Sent,
            'comment' => 'Application sent via email.',
        ]);

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent->value,
            'comment' => 'Application sent via email.',
        ]);
        $this->assertInstanceOf(ApplicationStatus::class, $history->fresh()->status);
    }

    public function test_history_entry_with_status_change_only(): void
    {
        $application = Application::factory()->create();

        $application->history()->create(['status' => ApplicationStatus::Invited]);

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Invited->value,
            'comment' => null,
        ]);
    }

    public function test_history_entry_as_standalone_note(): void
    {
        $application = Application::factory()->create();

        $application->history()->create(['comment' => 'Reminder: follow up next week.']);

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => null,
            'comment' => 'Reminder: follow up next week.',
        ]);
    }

    public function test_history_entry_allows_manual_created_at(): void
    {
        $application = Application::factory()->create();
        $pastDate = now()->subDays(30);

        $history = $application->history()->create([
            'status' => ApplicationStatus::Sent,
            'created_at' => $pastDate,
        ]);

        $this->assertTrue($history->created_at->isSameDay($pastDate));
    }

    public function test_history_entries_are_ordered_latest_first(): void
    {
        $application = Application::factory()->create();

        $older = $application->history()->create([
            'status' => ApplicationStatus::Sent,
            'created_at' => now()->subDays(10),
        ]);
        $newer = $application->history()->create([
            'status' => ApplicationStatus::Invited,
            'created_at' => now()->subDay(),
        ]);

        $this->assertTrue($application->history->first()->is($newer));
        $this->assertTrue($application->history->last()->is($older));
    }

    public function test_history_entry_belongs_to_application(): void
    {
        $application = Application::factory()->create();
        $history = ApplicationHistory::factory()->create(['application_id' => $application->id]);

        $this->assertTrue($history->application->is($application));
    }

    public function test_history_entries_are_preserved_when_application_is_soft_deleted(): void
    {
        $application = Application::factory()->create();
        ApplicationHistory::factory()->count(2)->create(['application_id' => $application->id]);

        $application->delete();

        $this->assertSoftDeleted($application);
        $this->assertDatabaseCount('applications_history', 2);
    }

    public function test_schema_has_no_notes_column_and_has_comment_column(): void
    {
        $this->assertFalse(Schema::hasColumn('applications', 'notes'));
        $this->assertTrue(Schema::hasColumn('applications', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('applications_history', 'comment'));
    }
}
