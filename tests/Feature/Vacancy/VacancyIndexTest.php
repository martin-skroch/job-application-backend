<?php

namespace Tests\Feature\Vacancy;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VacancyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('vacancies.index'))
            ->assertOk()
            ->assertSeeLivewire('pages::vacancies.index');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('vacancies.index'))->assertRedirect(route('login'));
    }

    public function test_bookmarked_filter_shows_only_vacancies_without_application(): void
    {
        $user = User::factory()->create();
        $bookmarked = Vacancy::factory()->for($user)->create(['company_name' => 'Bookmarked Alpha GmbH']);
        $applied = Application::factory()->for($user)->create([
            'vacancy_id' => Vacancy::factory()->for($user)->create(['company_name' => 'Applied Beta AG']),
        ]);

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->assertSee($bookmarked->company_name)
            ->assertDontSee($applied->vacancy->company_name);
    }

    public function test_applied_filter_shows_only_vacancies_with_application(): void
    {
        $user = User::factory()->create();
        $bookmarked = Vacancy::factory()->for($user)->create(['company_name' => 'Bookmarked Alpha GmbH']);
        $applied = Application::factory()->for($user)->create([
            'vacancy_id' => Vacancy::factory()->for($user)->create(['company_name' => 'Applied Beta AG']),
        ]);

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->set('filter', 'applied')
            ->assertSee($applied->vacancy->company_name)
            ->assertDontSee($bookmarked->company_name);
    }

    public function test_archived_filter_shows_only_archived_vacancies(): void
    {
        $user = User::factory()->create();
        $active = Vacancy::factory()->for($user)->create(['company_name' => 'Active Gamma GmbH']);
        $archived = Vacancy::factory()->for($user)->create(['company_name' => 'Archived Delta AG']);
        $archived->delete();

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->set('filter', 'archived')
            ->assertSee($archived->company_name)
            ->assertDontSee($active->company_name);
    }

    public function test_other_users_vacancies_are_not_shown(): void
    {
        $user = User::factory()->create();
        $foreign = Vacancy::factory()->create(['company_name' => 'Foreign Epsilon GmbH']);

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->assertDontSee($foreign->company_name);
    }

    public function test_vacancy_can_be_bookmarked_with_essentials_only(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->call('create')
            ->set('source', 'https://example.com/jobs/backend')
            ->set('title', 'Backend Developer')
            ->set('company_name', 'Example AG')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect();

        $vacancy = $user->vacancies()->first();

        $this->assertNotNull($vacancy);
        $this->assertSame('Backend Developer', $vacancy->title);
        $this->assertSame('https://example.com/jobs/backend', $vacancy->source->value());
        $this->assertNull($vacancy->application);
    }

    public function test_bookmarking_requires_nothing_but_validates_source(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test('pages::vacancies.index')
            ->set('source', 'ftp://example.com')
            ->call('save')
            ->assertHasErrors(['source' => 'url']);
    }

    public function test_bookmarking_stores_description(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->set('description', 'We are looking for a talented engineer.')
            ->call('save');

        $this->assertDatabaseHas('vacancies', [
            'user_id' => $user->id,
            'description' => 'We are looking for a talented engineer.',
        ]);
    }

    public function test_pasted_html_description_is_converted_to_markdown(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test('pages::vacancies.index')
            ->set('description', '<p>We offer <strong>remote</strong> work.</p>')
            ->assertSet('description', 'We offer **remote** work.');
    }

    public function test_bookmarking_does_not_create_application(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->set('title', 'Backend Developer')
            ->call('save');

        $this->assertDatabaseCount('vacancies', 1);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_vacancy_can_be_archived_and_restored(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test('pages::vacancies.index')
            ->call('archive', $vacancy->id);

        $this->assertSoftDeleted($vacancy);

        $component->call('restore', $vacancy->id);

        $this->assertNotSoftDeleted($vacancy);
    }

    public function test_other_users_vacancy_cannot_be_archived(): void
    {
        $vacancy = Vacancy::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test('pages::vacancies.index')
            ->call('archive', $vacancy->id);

        $this->assertNotSoftDeleted($vacancy);
    }

    public function test_archive_ignores_invalid_id(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test('pages::vacancies.index')
            ->call('archive', 'not-a-ulid')
            ->assertOk();
    }
}
