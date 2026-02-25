<?php

namespace Tests\Feature;

use App\Enum\ApplicationStatus;
use App\Mail\ApplicationMail;
use App\Models\Application;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SendApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_application_publishes_sends_mail_and_sets_status(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
            'title' => 'Software Developer',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendApplication');

        Mail::assertSent(ApplicationMail::class, function (ApplicationMail $mail) use ($application) {
            $envelope = $mail->envelope();

            return $mail->application->is($application)
                && $envelope->to[0]->address === 'hiring@company.com'
                && $envelope->from?->address === 'applicant@example.com'
                && $envelope->from?->name === $application->profile->name
                && $envelope->subject === 'Bewerbung als Software Developer';
        });

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent->value,
        ]);

        $application->refresh();
        $this->assertNotNull($application->public_id);
        $this->assertNotNull($application->published_at);
    }

    public function test_send_application_cannot_be_sent_when_status_is_already_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);
        $application->history()->create(['status' => ApplicationStatus::Sent->value]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendApplication');

        Mail::assertNothingSent();
    }

    public function test_send_application_can_be_sent_again_after_status_changed_from_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);
        $application->history()->create(['status' => ApplicationStatus::Sent->value]);
        $application->history()->create(['status' => ApplicationStatus::Invited->value]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendApplication');

        Mail::assertSent(ApplicationMail::class);
    }

    public function test_send_application_fails_silently_when_contact_email_is_missing(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => null,
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendApplication');

        Mail::assertNothingSent();
        $this->assertDatabaseEmpty('applications_history');
    }

    public function test_send_test_email_sends_to_profile_email_without_setting_status(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendTestEmail');

        Mail::assertSent(ApplicationMail::class, function (ApplicationMail $mail) use ($application) {
            $envelope = $mail->envelope();

            return $envelope->to[0]->address === 'applicant@example.com'
                && $envelope->from?->address === 'applicant@example.com'
                && $envelope->from?->name === $application->profile->name;
        });

        $this->assertDatabaseMissing('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent->value,
        ]);
    }

    public function test_send_test_email_fails_silently_when_profile_has_no_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => null]);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendTestEmail');

        Mail::assertNothingSent();
    }

    public function test_another_user_cannot_send_application(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $other = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);

        Livewire::actingAs($other)
            ->test('pages::applications.show', ['application' => $application])
            ->call('sendApplication')
            ->assertForbidden();

        Mail::assertNothingSent();
        $this->assertDatabaseEmpty('applications_history');
    }
}
