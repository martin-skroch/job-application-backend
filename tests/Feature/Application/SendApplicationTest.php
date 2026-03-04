<?php

namespace Tests\Feature\Application;

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

    public function test_show_page_send_button_is_disabled_when_status_is_sent(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);
        $application->history()->create(['status' => ApplicationStatus::Sent->value]);

        Livewire::actingAs($user)
            ->test('pages::applications.show', ['application' => $application])
            ->assertSeeHtml('disabled');
    }

    public function test_already_sent_issue_persists_when_comment_follows_sent_status(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => 'applicant@example.com']);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);
        $application->history()->create(['status' => ApplicationStatus::Sent->value]);
        $application->history()->create(['comment' => 'Follow-up note.', 'status' => null]);

        $component = Livewire::actingAs($user)
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false]);

        $this->assertContains(__('Application has already been sent.'), $component->instance()->issues);
    }

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => true])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => true])
            ->call('send');

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
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false])
            ->call('send')
            ->assertForbidden();

        Mail::assertNothingSent();
        $this->assertDatabaseEmpty('applications_history');
    }

    public function test_send_preview_shows_correct_issues_for_real_email(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create([
            'contact_email' => null,
            'profile_id' => null,
        ]);

        $component = Livewire::actingAs($user)
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false]);

        $this->assertContains(__('No profile selected.'), $component->instance()->issues);
        $this->assertContains(__('Contact email is missing.'), $component->instance()->issues);
    }

    public function test_send_preview_shows_correct_issues_for_test_email(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['email' => null]);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => null,
        ]);

        $component = Livewire::actingAs($user)
            ->test('applications.send-preview', ['application' => $application, 'isTest' => true]);

        $this->assertContains(__('Profile has no email address.'), $component->instance()->issues);
        $this->assertNotContains(__('Contact email is missing.'), $component->instance()->issues);
    }

    public function test_send_preview_shows_correct_from_and_to_for_real_email(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
        ]);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);

        $component = Livewire::actingAs($user)
            ->test('applications.send-preview', ['application' => $application, 'isTest' => false]);

        $this->assertEquals('Max Mustermann <max@example.com>', $component->instance()->from);
        $this->assertEquals('hiring@company.com', $component->instance()->to);
    }

    public function test_send_preview_shows_correct_from_and_to_for_test_email(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'name' => 'Max Mustermann',
            'email' => 'max@example.com',
        ]);
        $application = Application::factory()->for($user)->for($profile)->create([
            'contact_email' => 'hiring@company.com',
        ]);

        $component = Livewire::actingAs($user)
            ->test('applications.send-preview', ['application' => $application, 'isTest' => true]);

        $this->assertEquals('Max Mustermann <max@example.com>', $component->instance()->from);
        $this->assertEquals('max@example.com', $component->instance()->to);
    }
}
