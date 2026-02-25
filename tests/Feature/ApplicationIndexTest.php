<?php

namespace Tests\Feature;

use App\Enum\ApplicationStatus;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_filter_shows_only_draft_applications(): void
    {
        $user = User::factory()->create();
        $draft = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $draft->id, 'status' => ApplicationStatus::Draft]);
        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->assertSee($draft->company_name)
            ->assertDontSee($sent->company_name);
    }

    public function test_draft_filter_shows_only_applications_whose_latest_status_is_draft(): void
    {
        $user = User::factory()->create();

        $draft = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $draft->id, 'status' => ApplicationStatus::Draft]);

        $sentThenDraft = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sentThenDraft->id, 'status' => ApplicationStatus::Sent, 'created_at' => now()->subDay()]);
        ApplicationHistory::factory()->create(['application_id' => $sentThenDraft->id, 'status' => ApplicationStatus::Draft, 'created_at' => now()]);

        $sent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'draft')
            ->assertSee($draft->company_name)
            ->assertSee($sentThenDraft->company_name)
            ->assertDontSee($sent->company_name);
    }

    public function test_draft_filter_excludes_applications_whose_latest_status_is_not_draft(): void
    {
        $user = User::factory()->create();

        $draftThenSent = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $draftThenSent->id, 'status' => ApplicationStatus::Draft, 'created_at' => now()->subDay()]);
        ApplicationHistory::factory()->create(['application_id' => $draftThenSent->id, 'status' => ApplicationStatus::Sent, 'created_at' => now()]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('status', 'draft')
            ->assertDontSee($draftThenSent->company_name);
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

    public function test_creating_application_sets_draft_status(): void
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
        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Draft->value,
        ]);
    }

    public function test_updating_application_does_not_set_draft_status(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create();
        $application->history()->create(['status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->call('save');

        $this->assertDatabaseMissing('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Draft->value,
        ]);
    }

    public function test_draft_enum_value_is_draft(): void
    {
        $this->assertEquals('draft', ApplicationStatus::Draft->value);
    }

    public function test_migration_sets_draft_for_applications_without_history(): void
    {
        $user = User::factory()->create();
        $withoutHistory = Application::factory()->for($user)->create();
        $commentOnly = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $commentOnly->id, 'status' => null, 'comment' => 'A note']);
        $withStatus = Application::factory()->for($user)->create();
        ApplicationHistory::factory()->create(['application_id' => $withStatus->id, 'status' => ApplicationStatus::Sent]);

        $applicationIds = DB::table('applications')
            ->whereNull('deleted_at')
            ->whereNotExists(function ($query) {
                $query->from('applications_history')
                    ->whereColumn('applications_history.application_id', 'applications.id')
                    ->whereNotNull('applications_history.status');
            })
            ->pluck('id');

        foreach ($applicationIds as $applicationId) {
            DB::table('applications_history')->insert([
                'id' => (string) Str::ulid(),
                'application_id' => $applicationId,
                'status' => 'draft',
                'comment' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $withoutHistory->id,
            'status' => ApplicationStatus::Draft->value,
        ]);
        $this->assertDatabaseHas('applications_history', [
            'application_id' => $commentOnly->id,
            'status' => ApplicationStatus::Draft->value,
        ]);
        $this->assertDatabaseMissing('applications_history', [
            'application_id' => $withStatus->id,
            'status' => ApplicationStatus::Draft->value,
        ]);
    }
}
