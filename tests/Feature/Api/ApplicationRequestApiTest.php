<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ApplicationRequestedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_creates_vacancy_and_linked_application(): void
    {
        Notification::fake();
        $user = Sanctum::actingAs(User::factory()->create());

        $this->postJson(route('api.application.request'), [
            'name' => 'Example AG',
            'email' => 'jobs@example.com',
        ], ['referer' => 'https://example.com/apply'])
            ->assertSuccessful();

        $vacancy = $user->vacancies()->first();
        $application = $user->applications()->first();

        $this->assertNotNull($vacancy);
        $this->assertNotNull($application);
        $this->assertTrue($application->vacancy->is($vacancy));
        $this->assertSame('https://example.com/apply', $vacancy->source->value());
        $this->assertSame('jobs@example.com', $application->contact_email);
        $this->assertSame('Example AG', $application->company_name);

        Notification::assertSentTo($user, ApplicationRequestedNotification::class);
    }
}
