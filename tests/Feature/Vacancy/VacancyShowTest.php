<?php

namespace Tests\Feature\Vacancy;

use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_page_displays_vacancy(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('vacancies.show', $vacancy))
            ->assertOk()
            ->assertSee($vacancy->company_name)
            ->assertSee($vacancy->title)
            ->assertSee(__('Write application'));
    }

    public function test_show_page_links_to_existing_application(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('vacancies.show', $application->vacancy))
            ->assertOk()
            ->assertSee(route('applications.show', $application))
            ->assertDontSee(__('Write application'));
    }

    public function test_other_users_cannot_view_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('vacancies.show', $vacancy))
            ->assertForbidden();
    }

    public function test_application_show_page_links_to_vacancy(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('applications.show', $application))
            ->assertOk()
            ->assertSee($application->vacancy->title)
            ->assertSee(route('vacancies.show', $application->vacancy));
    }
}
