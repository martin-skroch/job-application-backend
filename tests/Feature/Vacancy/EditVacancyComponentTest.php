<?php

namespace Tests\Feature\Vacancy;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditVacancyComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_alpine_scope_does_not_shadow_the_open_action(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->assertSeeHtml('wire:click="open"')
            ->assertDontSeeHtml('x-data="{ open: false }"');
    }

    public function test_open_fills_form_from_vacancy(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create([
            'title' => 'Backend Developer',
            'company_name' => 'Acme GmbH',
            'description' => 'Loaded from the database.',
        ]);

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->set('title', 'Changed')
            ->call('open')
            ->assertSet('title', 'Backend Developer')
            ->assertSet('company_name', 'Acme GmbH')
            ->assertSet('description', 'Loaded from the database.');
    }

    public function test_vacancy_can_be_saved(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->set('title', 'Senior Developer')
            ->set('company_name', 'Example AG')
            ->set('company_website', 'https://example.com')
            ->set('description', 'Updated description.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('vacancy-saved');

        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'title' => 'Senior Developer',
            'company_name' => 'Example AG',
            'description' => 'Updated description.',
        ]);
    }

    public function test_saving_vacancy_does_not_change_application_recipient(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create(['company_name' => 'Original GmbH']);

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $application->vacancy])
            ->set('company_name', 'Renamed GmbH')
            ->call('save');

        $this->assertSame('Original GmbH', $application->fresh()->company_name);
    }

    public function test_source_must_be_a_valid_url(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->set('source', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['source' => 'url']);
    }

    public function test_other_users_cannot_open_the_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->assertForbidden();
    }

    public function test_vacancy_without_application_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy])
            ->assertSee(__('Delete vacancy'))
            ->call('delete')
            ->assertHasNoErrors()
            ->assertRedirect(route('vacancies.index'));

        $this->assertDatabaseMissing('vacancies', ['id' => $vacancy->id]);
    }

    public function test_vacancy_with_application_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $application->vacancy])
            ->assertSee(__('This vacancy cannot be deleted because an application has been written for it.'))
            ->call('delete')
            ->assertHasErrors(['delete'])
            ->assertNoRedirect();

        $this->assertDatabaseHas('vacancies', ['id' => $application->vacancy_id]);
        $this->assertDatabaseHas('applications', ['id' => $application->id]);
    }

    public function test_vacancy_with_archived_application_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $application->delete();

        Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $application->vacancy])
            ->call('delete')
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('applications', ['id' => $application->id]);
    }

    public function test_delete_is_forbidden_when_vacancy_belongs_to_another_user(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test('vacancies.edit', ['vacancy' => $vacancy]);

        $vacancy->forceFill(['user_id' => User::factory()->create()->id])->save();

        $component->call('delete')->assertForbidden();

        $this->assertDatabaseHas('vacancies', ['id' => $vacancy->id]);
    }
}
