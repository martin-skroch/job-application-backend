<?php

namespace Tests\Feature\Application;

use App\Enum\ApplicationStatus;
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

        $invited = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $invited->id, 'status' => ApplicationStatus::Invited]);

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'draft')
            ->assertDontSee($invited->company_name)
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

    public function test_open_loads_earliest_entry_date_into_form(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'earliest_entry_date' => '2026-06-01',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->assertSet('earliest_entry_date', '2026-06-01');
    }

    public function test_updating_application_stores_recipient_details(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->assertSet('company_name', $application->company_name)
            ->set('contact_name', 'Erika Mustermann')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'contact_name' => 'Erika Mustermann',
        ]);
        $this->assertNotSame('Erika Mustermann', $application->vacancy->fresh()->contact_name);
    }

    public function test_open_cannot_load_another_users_application(): void
    {
        $application = Application::factory()->for(User::factory())->create();

        Livewire::actingAs(User::factory()->create())
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->assertNotFound()
            ->assertSet('applicationId', null);
    }

    public function test_index_links_to_vacancies_instead_of_creating_applications(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test('pages::applications.index')
            ->assertSee(route('vacancies.index'));
    }

    public function test_index_shows_vacancy_title(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->assertSee($application->vacancy->title);
    }
}
