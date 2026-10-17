<?php

namespace Tests\Feature\Vacancy;

use App\Actions\CreateApplicationFromVacancy;
use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use App\Models\Profile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CreateApplicationFromVacancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_action_copies_company_and_contact_from_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create();

        $application = app(CreateApplicationFromVacancy::class)->handle($vacancy, [
            'form_of_address' => FormOfAddress::Formal,
        ]);

        $this->assertTrue($application->vacancy->is($vacancy));
        $this->assertSame($vacancy->user_id, $application->user_id);

        foreach (CreateApplicationFromVacancy::COPIED_ATTRIBUTES as $attribute) {
            $this->assertEquals((string) $vacancy->{$attribute}, (string) $application->{$attribute}, $attribute);
        }
    }

    public function test_action_rejects_second_application_for_same_vacancy(): void
    {
        $application = Application::factory()->create();

        $this->expectException(ValidationException::class);

        app(CreateApplicationFromVacancy::class)->handle($application->vacancy);
    }

    public function test_component_creates_application_and_redirects(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->call('open')
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->call('save')
            ->assertHasNoErrors();

        $application = $vacancy->application()->first();

        $this->assertNotNull($application);
        $this->assertSame($profile->id, $application->profile_id);
        $this->assertSame($vacancy->company_name, $application->company_name);
        $component->assertRedirect(route('applications.show', $application));
    }

    public function test_creating_application_does_not_create_history_entry(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->call('save');

        $this->assertDatabaseCount('applications', 1);
        $this->assertDatabaseCount('applications_history', 0);
    }

    public function test_creating_application_stores_earliest_entry_date(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->set('earliest_entry_date', '2026-06-01')
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'earliest_entry_date' => '2026-06-01 00:00:00',
        ]);
    }

    public function test_earliest_entry_date_is_optional(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value)
            ->set('earliest_entry_date', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($vacancy->application()->first()->earliest_entry_date);
    }

    public function test_profile_is_required(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->call('save')
            ->assertHasErrors(['profile_id' => 'required']);

        $this->assertDatabaseCount('applications', 0);
    }

    public function test_salary_desire_is_required_when_overriding(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Override->value)
            ->call('save')
            ->assertHasErrors(['salary_desire' => 'required']);
    }

    public function test_another_users_profile_cannot_be_used(): void
    {
        $user = User::factory()->create();
        $foreignProfile = Profile::factory()->for(User::factory())->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        try {
            Livewire::actingAs($user)
                ->test('vacancies.create-application', ['vacancy' => $vacancy])
                ->set('profile_id', $foreignProfile->id)
                ->set('salary_behavior', SalaryBehaviors::Hidden->value)
                ->call('save');

            $this->fail('Expected a ModelNotFoundException for a foreign profile.');
        } catch (ModelNotFoundException) {
            $this->assertDatabaseCount('applications', 0);
        }
    }

    public function test_other_users_cannot_write_application_for_vacancy(): void
    {
        $vacancy = Vacancy::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->assertForbidden();
    }

    public function test_component_shows_link_when_application_exists(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $application->vacancy])
            ->assertSee(route('applications.show', $application))
            ->assertDontSee(__('Write application'));
    }

    public function test_second_application_shows_validation_error(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $vacancy = Vacancy::factory()->for($user)->create();

        $component = Livewire::actingAs($user)
            ->test('vacancies.create-application', ['vacancy' => $vacancy])
            ->set('profile_id', $profile->id)
            ->set('salary_behavior', SalaryBehaviors::Hidden->value);

        Application::factory()->for($user)->create(['vacancy_id' => $vacancy->id]);

        $component->call('save')->assertHasErrors(['vacancy']);

        $this->assertDatabaseCount('applications', 1);
    }
}
