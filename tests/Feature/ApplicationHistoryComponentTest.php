<?php

namespace Tests\Feature;

use App\Enum\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationHistoryComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_entries_are_displayed(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->assertSee($entry->status->name);
    }

    public function test_edit_loads_entry_data_into_form(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
            'comment' => 'Initial comment',
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->assertSet('editingId', $entry->id)
            ->assertSet('editStatus', ApplicationStatus::Sent->value)
            ->assertSet('editComment', 'Initial comment');
    }

    public function test_update_saves_changes_to_history_entry(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
            'comment' => 'Old comment',
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->set('editStatus', ApplicationStatus::Invited->value)
            ->set('editComment', 'Updated comment')
            ->set('editDate', '2026-01-15T10:00')
            ->call('update');

        $this->assertDatabaseHas('applications_history', [
            'id' => $entry->id,
            'status' => ApplicationStatus::Invited->value,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_update_clears_editing_state_after_save(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->set('editStatus', ApplicationStatus::Sent->value)
            ->set('editDate', '2026-01-15T10:00')
            ->call('update')
            ->assertSet('editingId', null);
    }

    public function test_update_requires_comment_when_no_status_selected(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'comment' => 'Some note',
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->set('editStatus', '')
            ->set('editComment', '')
            ->set('editDate', '2026-01-15T10:00')
            ->call('update')
            ->assertHasErrors(['editComment']);
    }

    public function test_cancel_edit_clears_editing_state(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->assertSet('editingId', $entry->id)
            ->call('cancelEdit')
            ->assertSet('editingId', null);
    }

    public function test_delete_removes_history_entry(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('delete', $entry->id);

        $this->assertDatabaseMissing('applications_history', ['id' => $entry->id]);
    }

    public function test_delete_clears_editing_state_if_entry_was_being_edited(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->call('delete', $entry->id)
            ->assertSet('editingId', null);
    }

    public function test_update_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('edit', $entry->id)
            ->set('editStatus', ApplicationStatus::Invited->value)
            ->set('editDate', '2026-01-15T10:00')
            ->call('update')
            ->assertForbidden();

        $this->assertDatabaseHas('applications_history', [
            'id' => $entry->id,
            'status' => ApplicationStatus::Sent->value,
        ]);
    }

    public function test_delete_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();
        $entry = ApplicationHistory::factory()->create([
            'application_id' => $application->id,
            'status' => ApplicationStatus::Sent,
        ]);

        Livewire::actingAs($user)
            ->test('applications.history', ['application' => $application])
            ->call('delete', $entry->id)
            ->assertForbidden();

        $this->assertDatabaseHas('applications_history', ['id' => $entry->id]);
    }
}
