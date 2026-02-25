<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_restore_restores_archived_application(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $application->delete();

        $this->assertSoftDeleted($application);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('restore', $application->id);

        $this->assertNotSoftDeleted($application);
        $this->assertDatabaseHas('applications_history', [
            'application_id' => $application->id,
            'status' => null,
            'comment' => 'Application restored from archive.',
        ]);
    }

    public function test_restore_does_nothing_for_non_archived_application(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('restore', $application->id);

        $this->assertNotSoftDeleted($application);
        $this->assertNull($application->fresh()->deleted_at);
    }

    public function test_restore_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();
        $application->delete();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('restore', $application->id);

        $this->assertSoftDeleted($application);
    }

    public function test_archive_soft_deletes_application(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('archive', $application->id);

        $this->assertSoftDeleted($application);
        $this->assertTrue($application->fresh()->isArchived());
    }

    public function test_archive_does_nothing_for_invalid_id(): void
    {
        $user = User::factory()->create();
        Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('archive', 'not-a-ulid');

        $this->assertEquals(1, Application::withTrashed()->count());
        $this->assertNull(Application::first()->deleted_at);
    }

    public function test_archive_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('archive', $application->id);

        $this->assertNotSoftDeleted($application);
    }
}
