<?php

namespace Tests\Feature;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationStatusModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_status_creates_history_entry_with_status_and_comment(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', ApplicationStatus::Sent->value)
            ->set('statusComment', 'Sent via email.')
            ->set('statusDate', now()->toDateString())
            ->call('save');

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent->value,
            'comment' => 'Sent via email.',
        ]);
    }

    public function test_save_status_creates_standalone_note_without_status(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', null)
            ->set('statusComment', 'Remember to follow up.')
            ->set('statusDate', now()->toDateString())
            ->call('save');

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => null,
            'comment' => 'Remember to follow up.',
        ]);
    }

    public function test_save_status_respects_custom_datetime(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $customDatetime = '2026-01-10T14:30';

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', ApplicationStatus::Sent->value)
            ->set('statusDate', $customDatetime)
            ->call('save');

        $history = $application->history()->first();
        $this->assertEquals('2026-01-10', $history->created_at->toDateString());
        $this->assertEquals('14:30', $history->created_at->format('H:i'));
    }

    public function test_save_status_requires_comment_when_no_status_selected(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', null)
            ->set('statusComment', '')
            ->call('save')
            ->assertHasErrors(['statusComment']);
    }

    public function test_save_status_rejects_invalid_status_value(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', 'invalid_status')
            ->set('statusDate', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['newStatus']);
    }

    public function test_save_status_uses_current_date_when_no_date_provided(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', ApplicationStatus::Sent->value)
            ->set('statusDate', '')
            ->call('save');

        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent->value,
        ]);

        $history = $application->history()->first();
        $this->assertEquals(now()->toDateString(), $history->created_at->toDateString());
    }

    public function test_save_status_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::actingAs($user)
            ->test('application-status-modal', ['application' => $application])
            ->set('newStatus', ApplicationStatus::Sent->value)
            ->set('statusDate', now()->toDateString())
            ->call('save');

        $this->assertDatabaseEmpty('applications_history');
    }
}
