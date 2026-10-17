<?php

namespace Tests\Feature\Vacancy;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_description_is_stored_and_retrieved(): void
    {
        $vacancy = Vacancy::factory()->create([
            'description' => 'We are looking for a senior developer.',
        ]);

        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'description' => 'We are looking for a senior developer.',
        ]);
        $this->assertEquals('We are looking for a senior developer.', $vacancy->fresh()->description);
    }

    public function test_description_can_be_null(): void
    {
        $vacancy = Vacancy::factory()->create(['description' => null]);

        $this->assertNull($vacancy->fresh()->description);
    }

    public function test_vacancy_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $this->assertTrue($vacancy->user->is($user));
        $this->assertTrue($user->vacancies()->first()->is($vacancy));
    }

    public function test_application_relation_includes_archived_applications(): void
    {
        $application = Application::factory()->create();
        $application->delete();

        $this->assertTrue($application->vacancy->fresh()->application->is($application));
    }

    public function test_vacancy_can_only_have_one_application(): void
    {
        $application = Application::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Application::factory()->create(['vacancy_id' => $application->vacancy_id]);
    }

    public function test_is_archived_reflects_soft_delete(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->assertFalse($vacancy->isArchived());

        $vacancy->delete();

        $this->assertTrue($vacancy->isArchived());
    }
}
