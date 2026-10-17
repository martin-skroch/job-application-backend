<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VacancyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacancy_can_be_bookmarked_via_api(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.vacancies.store'), [
            'source' => 'https://example.com/jobs/backend',
            'title' => 'Backend Developer',
            'company_name' => 'Example AG',
        ])
            ->assertCreated()
            ->assertJsonPath('title', 'Backend Developer')
            ->assertJsonPath('source', 'https://example.com/jobs/backend')
            ->assertJsonPath('company_name', 'Example AG');

        $this->assertDatabaseHas('vacancies', [
            'user_id' => $user->id,
            'title' => 'Backend Developer',
        ]);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_existing_vacancy_with_same_source_is_returned(): void
    {
        $user = Sanctum::actingAs(User::factory()->create());
        $existing = Vacancy::factory()->for($user)->create(['source' => 'https://example.com/jobs/backend']);

        $this->postJson(route('api.vacancies.store'), [
            'source' => 'https://example.com/jobs/backend',
            'title' => 'Different title',
        ])
            ->assertOk()
            ->assertJsonPath('id', $existing->id);

        $this->assertDatabaseCount('vacancies', 1);
    }

    public function test_same_source_of_another_user_creates_new_vacancy(): void
    {
        Vacancy::factory()->create(['source' => 'https://example.com/jobs/backend']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.vacancies.store'), [
            'source' => 'https://example.com/jobs/backend',
        ])->assertCreated();

        $this->assertDatabaseCount('vacancies', 2);
    }

    public function test_source_must_be_a_valid_url(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.vacancies.store'), [
            'source' => 'not-a-url',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('source');
    }

    public function test_guests_cannot_bookmark_vacancies(): void
    {
        $this->postJson(route('api.vacancies.store'), [
            'source' => 'https://example.com/jobs/backend',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('vacancies', 0);
    }
}
