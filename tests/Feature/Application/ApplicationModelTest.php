<?php

namespace Tests\Feature\Application;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_public_returns_true_when_published_at_and_public_id_are_set(): void
    {
        $application = Application::factory()->create([
            'published_at' => now(),
            'public_id' => 'somepublicid',
        ]);

        $this->assertTrue($application->isPublic());
    }

    public function test_is_public_returns_false_when_public_id_is_empty(): void
    {
        $application = Application::factory()->create([
            'published_at' => now(),
            'public_id' => null,
        ]);

        $this->assertFalse($application->isPublic());
    }

    public function test_is_public_returns_false_when_published_at_is_null(): void
    {
        $application = Application::factory()->create([
            'published_at' => null,
            'public_id' => 'somepublicid',
        ]);

        $this->assertFalse($application->isPublic());
    }

    public function test_is_archived_returns_true_when_soft_deleted(): void
    {
        $application = Application::factory()->create();
        $application->delete();

        $application->refresh();
        $this->assertTrue($application->isArchived());
    }

    public function test_is_archived_returns_false_when_not_deleted(): void
    {
        $application = Application::factory()->create();

        $this->assertFalse($application->isArchived());
    }

    public function test_status_returns_latest_status(): void
    {
        $application = Application::factory()->create();
        $application->history()->create(['status' => ApplicationStatus::Bookmarked, 'created_at' => now()->subDay()]);
        $application->history()->create(['status' => ApplicationStatus::Sent, 'created_at' => now()]);

        $this->assertEquals(ApplicationStatus::Sent, $application->status());
    }

    public function test_status_returns_null_when_no_status_history(): void
    {
        $application = Application::factory()->create();

        $this->assertNull($application->status());
    }

    public function test_status_ignores_comment_only_history_entries(): void
    {
        $application = Application::factory()->create();
        $application->history()->create(['comment' => 'Just a note.', 'created_at' => now()]);
        $application->history()->create(['status' => ApplicationStatus::Bookmarked, 'created_at' => now()->subDay()]);

        $this->assertEquals(ApplicationStatus::Bookmarked, $application->status());
    }

    public function test_latest_status_entry_returns_most_recent_status_entry(): void
    {
        $application = Application::factory()->create();
        $application->history()->create(['status' => ApplicationStatus::Sent, 'created_at' => now()->subDays(2)]);
        $latest = $application->history()->create(['status' => ApplicationStatus::Invited, 'created_at' => now()]);

        $this->assertTrue($application->latestStatusEntry->is($latest));
    }

    public function test_latest_status_entry_is_null_when_no_status_history(): void
    {
        $application = Application::factory()->create();
        $application->history()->create(['comment' => 'Only a comment.']);

        $this->assertNull($application->latestStatusEntry);
    }

    public function test_application_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->assertTrue($application->user->is($user));
    }

    public function test_public_id_is_unique_across_applications(): void
    {
        $publicId = 'uniqueid123';

        Application::factory()->create(['public_id' => $publicId]);
        $second = Application::factory()->create(['public_id' => null]);

        $this->assertDatabaseCount('applications', 2);
        $this->assertNull($second->public_id);
    }

    public function test_description_is_stored_and_retrieved(): void
    {
        $application = Application::factory()->create([
            'description' => 'We are looking for a senior developer.',
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'description' => 'We are looking for a senior developer.',
        ]);
        $this->assertEquals('We are looking for a senior developer.', $application->fresh()->description);
    }

    public function test_description_can_be_null(): void
    {
        $application = Application::factory()->create(['description' => null]);

        $this->assertNull($application->fresh()->description);
    }
}
