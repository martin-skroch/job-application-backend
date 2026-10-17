<?php

namespace Tests\Feature\Application;

use App\Models\Application;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditJobListingComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_alpine_scope_does_not_shadow_the_open_action(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create();

        Livewire::actingAs($user)
            ->test('applications.edit-job-listing', ['application' => $application])
            ->assertSeeHtml('wire:click="open"')
            ->assertDontSeeHtml('x-data="{ open: false }"');
    }

    public function test_open_fills_form_from_application(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'title' => 'Backend Developer',
            'company_name' => 'Acme GmbH',
        ]);

        Livewire::actingAs($user)
            ->test('applications.edit-job-listing', ['application' => $application])
            ->set('title', 'Changed')
            ->call('open')
            ->assertSet('title', 'Backend Developer')
            ->assertSet('company_name', 'Acme GmbH');
    }

    public function test_job_listing_can_be_saved(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create();

        Livewire::actingAs($user)
            ->test('applications.edit-job-listing', ['application' => $application])
            ->set('title', 'Senior Developer')
            ->set('company_name', 'Example AG')
            ->set('company_website', 'https://example.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('application-saved');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'title' => 'Senior Developer',
            'company_name' => 'Example AG',
        ]);
    }

    public function test_other_users_cannot_open_the_job_listing(): void
    {
        $owner = User::factory()->create();
        $profile = Profile::factory()->for($owner)->create();
        $application = Application::factory()->for($owner)->for($profile)->create();

        Livewire::actingAs(User::factory()->create())
            ->test('applications.edit-job-listing', ['application' => $application])
            ->assertForbidden();
    }
}
