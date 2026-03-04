<?php

namespace Tests\Feature\Application;

use App\Enum\ApplicationStatus;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_filter_shows_only_draft_applications(): void
    {
        $user = User::factory()->create();
        $draft = Application::factory()->for($user)->create();
        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->assertSee($draft->company_name)
            ->assertDontSee($sent->company_name);
    }

    public function test_draft_filter_shows_applications_with_no_status_entries(): void
    {
        $user = User::factory()->create();

        $noHistory = Application::factory()->for($user)->create();

        $commentOnly = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $commentOnly->id, 'status' => null, 'comment' => 'Just a note']);

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'draft')
            ->assertSee($noHistory->company_name)
            ->assertSee($commentOnly->company_name)
            ->assertDontSee($sent->company_name);
    }

    public function test_draft_filter_excludes_applications_with_any_status_entry(): void
    {
        $user = User::factory()->create();

        $bookmarked = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $bookmarked->id, 'status' => ApplicationStatus::Bookmarked]);

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'draft')
            ->assertDontSee($bookmarked->company_name)
            ->assertDontSee($sent->company_name);
    }

    public function test_sent_filter_shows_only_sent_applications(): void
    {
        $user = User::factory()->create();
        $draft = Application::factory()->for($user)->create();
        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'sent')
            ->assertSee($sent->company_name)
            ->assertDontSee($draft->company_name);
    }

    public function test_bookmarked_filter_shows_only_bookmarked_applications(): void
    {
        $user = User::factory()->create();

        $bookmarked = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $bookmarked->id, 'status' => ApplicationStatus::Bookmarked]);

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        $noStatus = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'bookmarked')
            ->assertSee($bookmarked->company_name)
            ->assertDontSee($sent->company_name)
            ->assertDontSee($noStatus->company_name);
    }

    public function test_creating_application_does_not_create_history_entry(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->call('save');

        $application = $user->applications()->first();

        $this->assertNotNull($application);
        $this->assertDatabaseMissing('applications_history', [
            'application_id' => $application->id,
        ]);
    }

    public function test_updating_application_does_not_create_history_entry(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create();
        $application->history()->create(['status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->call('save');

        $this->assertDatabaseCount('applications_history', 1);
    }

    public function test_creating_application_stores_description(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->set('description', 'We are looking for a talented engineer.')
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'description' => 'We are looking for a talented engineer.',
        ]);
    }

    public function test_updating_application_stores_description(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'description' => 'Old description.',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->set('description', 'Updated description.')
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'description' => 'Updated description.',
        ]);
    }

    public function test_open_loads_description_into_form(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'description' => 'Loaded from the database.',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->assertSet('description', 'Loaded from the database.');
    }
}
