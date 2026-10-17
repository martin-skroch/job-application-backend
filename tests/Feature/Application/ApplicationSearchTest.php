<?php

namespace Tests\Feature\Application;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_company_name(): void
    {
        $user = User::factory()->create();
        $matching = Application::factory()->for($user)->create(['company_name' => 'Acme Corporation']);
        $other = Application::factory()->for($user)->create(['company_name' => 'Other GmbH']);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Acme')
            ->assertSee($matching->company_name)
            ->assertDontSee($other->company_name);
    }

    public function test_search_filters_by_title(): void
    {
        $user = User::factory()->create();
        $matching = Application::factory()->for($user)->create([
            'vacancy_id' => Vacancy::factory()->for($user)->create(['title' => 'Senior PHP Developer']),
            'company_name' => 'PHP Corp',
        ]);
        $other = Application::factory()->for($user)->create([
            'vacancy_id' => Vacancy::factory()->for($user)->create(['title' => 'Marketing Manager']),
            'company_name' => 'Marketing Inc',
        ]);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'PHP')
            ->assertSee($matching->company_name)
            ->assertDontSee($other->company_name);
    }

    public function test_search_filters_by_contact_name(): void
    {
        $user = User::factory()->create();
        $matching = Application::factory()->for($user)->create(['contact_name' => 'Max Mustermann', 'company_name' => 'Mustermann GmbH']);
        $other = Application::factory()->for($user)->create(['contact_name' => 'Jane Doe', 'company_name' => 'Doe Ltd']);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Mustermann')
            ->assertSee($matching->company_name)
            ->assertDontSee($other->company_name);
    }

    public function test_search_ignores_status_filter_and_shows_all_matching_applications(): void
    {
        $user = User::factory()->create();
        $draft = Application::factory()->for($user)->create(['company_name' => 'Acme Draft']);
        $sent = Application::factory()->for($user)->create(['company_name' => 'Acme Sent']);
        ApplicationHistory::factory()->create(['application_id' => $sent->id, 'status' => ApplicationStatus::Sent]);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Acme')
            ->assertSee($draft->company_name)
            ->assertSee($sent->company_name);
    }

    public function test_search_includes_archived_applications(): void
    {
        $user = User::factory()->create();
        $active = Application::factory()->for($user)->create(['company_name' => 'Acme Active']);
        $archived = Application::factory()->for($user)->create(['company_name' => 'Acme Archived']);
        $archived->delete();

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Acme')
            ->assertSee($active->company_name)
            ->assertSee($archived->company_name);
    }

    public function test_search_returns_empty_for_short_terms(): void
    {
        $user = User::factory()->create();
        Application::factory()->for($user)->create(['company_name' => 'Acme Corporation']);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'A')
            ->assertDontSee('Acme Corporation');
    }

    public function test_search_limits_to_eight_results(): void
    {
        $user = User::factory()->create();
        Application::factory()->count(10)->for($user)->create(['company_name' => 'Search Match Co']);

        $component = Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Search Match');

        $this->assertCount(8, $component->get('results'));
    }

    public function test_search_finds_bookmarked_vacancies_without_application(): void
    {
        $user = User::factory()->create();
        $bookmarked = Vacancy::factory()->for($user)->create(['company_name' => 'Bookmark AG']);
        $applied = Application::factory()->for($user)->create(['company_name' => 'Bookmark Applied AG']);

        $component = Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Bookmark')
            ->assertSee($bookmarked->company_name)
            ->assertSee(route('vacancies.show', $bookmarked))
            ->assertSee($applied->company_name);

        $this->assertCount(1, $component->get('vacancyResults'));
    }

    public function test_search_does_not_find_other_users_vacancies(): void
    {
        $user = User::factory()->create();
        $foreign = Vacancy::factory()->create(['company_name' => 'Foreign Corp']);

        Livewire::actingAs($user)
            ->test('applications.search')
            ->set('search', 'Foreign')
            ->assertDontSee($foreign->company_name);
    }
}
