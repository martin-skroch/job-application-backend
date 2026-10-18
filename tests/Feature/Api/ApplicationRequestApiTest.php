<?php

namespace Tests\Feature\Api;

use App\Models\Application;
use App\Models\User;
use App\Notifications\ApplicationRequestedNotification;
use App\Notifications\VacancyRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_without_id_creates_vacancy_and_notifies_user(): void
    {
        Notification::fake();
        $user = Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.application.request'), [
            'name' => 'Example AG',
            'email' => 'jobs@example.com',
        ], ['referer' => 'https://example.com/apply'])
            ->assertSuccessful();

        $vacancy = $user->vacancies()->first();

        $this->assertNotNull($vacancy);
        $this->assertSame(0, $user->applications()->count());
        $this->assertSame('https://example.com/apply', $vacancy->source->value());
        $this->assertSame('jobs@example.com', $vacancy->contact_email);
        $this->assertSame('Example AG', $vacancy->company_name);

        Notification::assertSentTo($user, VacancyRequestedNotification::class, function (VacancyRequestedNotification $notification) use ($vacancy, $user): bool {
            $mail = $notification->toMail($user);

            return $notification->vacancy->is($vacancy)
                && $mail->replyTo === [['jobs@example.com', null]]
                && $mail->actionUrl === route('vacancies.show', $vacancy);
        });
        Notification::assertNotSentTo($user, ApplicationRequestedNotification::class);
    }

    public function test_request_with_unknown_id_creates_vacancy(): void
    {
        Notification::fake();
        $user = Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.application.request'), [
            'id' => 'does-not-exist',
            'name' => 'Example AG',
            'email' => 'jobs@example.com',
        ])->assertSuccessful();

        $this->assertSame(1, $user->vacancies()->count());
        Notification::assertSentTo($user, VacancyRequestedNotification::class);
    }

    public function test_request_without_id_requires_contact_data(): void
    {
        Notification::fake();
        $user = Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.application.request'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email']);

        $this->assertSame(0, $user->vacancies()->count());
        Notification::assertNothingSent();
    }

    public function test_request_with_existing_id_creates_analytics_and_notifies_user(): void
    {
        Notification::fake();
        $user = Sanctum::actingAs(User::factory()->create());
        $application = Application::factory()->for($user)->create(['public_id' => 'public-123']);

        $this->postJson(route('api.application.request'), ['id' => 'public-123'])
            ->assertSuccessful();

        $this->assertSame(1, $application->analytics()->count());
        $this->assertSame(1, $user->vacancies()->count());

        Notification::assertSentTo($user, ApplicationRequestedNotification::class);
        Notification::assertNotSentTo($user, VacancyRequestedNotification::class);
    }
}
