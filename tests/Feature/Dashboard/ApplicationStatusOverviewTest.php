<?php

namespace Tests\Feature\Dashboard;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationStatusOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_status_overview_component(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSeeLivewire('dashboard.application-status-overview');
    }

    public function test_draft_count_includes_applications_without_any_history(): void
    {
        $user = User::factory()->create();
        Application::factory()->count(2)->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));
        $draft = $counts->firstWhere('label', __('Draft'));

        $this->assertNotNull($draft);
        $this->assertEquals(2, $draft['count']);
    }

    public function test_draft_count_includes_applications_with_only_comments(): void
    {
        $user = User::factory()->create();

        $commentOnly = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create([
            'application_id' => $commentOnly->id,
            'status' => null,
            'comment' => 'Just a note',
        ]);

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));
        $draft = $counts->firstWhere('label', __('Draft'));

        $this->assertNotNull($draft);
        $this->assertEquals(1, $draft['count']);
    }

    public function test_comment_only_count_shows_applications_with_only_comment_history(): void
    {
        $user = User::factory()->create();

        $commentOnly = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create([
            'application_id' => $commentOnly->id,
            'status' => null,
            'comment' => 'Note without status',
        ]);

        Application::factory()->for($user)->create(); // pure draft, no history

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));
        $withComments = $counts->firstWhere('label', __('With Comments'));

        $this->assertNotNull($withComments);
        $this->assertEquals(1, $withComments['count']);
    }

    public function test_sent_count_reflects_applications_with_sent_status(): void
    {
        $user = User::factory()->create();

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create([
            'application_id' => $sent->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Application::factory()->for($user)->create(); // draft

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));
        $sentItem = $counts->firstWhere('value', ApplicationStatus::Sent->value);

        $this->assertNotNull($sentItem);
        $this->assertEquals(1, $sentItem['count']);
    }

    public function test_status_counts_only_include_current_users_applications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherApp = Application::factory()->for($otherUser)->create();
        ApplicationHistory::factory()->create([
            'application_id' => $otherApp->id,
            'status' => ApplicationStatus::Sent,
        ]);

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));
        $sentItem = $counts->firstWhere('value', ApplicationStatus::Sent->value);

        $this->assertEquals(0, $sentItem['count']);
    }

    public function test_application_with_overridden_status_counted_only_for_latest_status(): void
    {
        $user = User::factory()->create();

        $app = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create([
            'application_id' => $app->id,
            'status' => ApplicationStatus::Sent,
            'created_at' => now()->subDay(),
        ]);
        ApplicationHistory::factory()->create([
            'application_id' => $app->id,
            'status' => ApplicationStatus::Invited,
            'created_at' => now(),
        ]);

        $component = Livewire::actingAs($user)
            ->test('dashboard.application-status-overview');

        $counts = collect($component->get('statusCounts'));

        $sentItem = $counts->firstWhere('value', ApplicationStatus::Sent->value);
        $invitedItem = $counts->firstWhere('value', ApplicationStatus::Invited->value);

        $this->assertEquals(0, $sentItem['count']);
        $this->assertEquals(1, $invitedItem['count']);
    }
}
