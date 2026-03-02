<?php

namespace Tests\Feature;

use App\Models\Analytics;
use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnalyticsDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_all_entries_for_the_given_session(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        $session = 'abc123';
        Analytics::create(['application_id' => $application->id, 'session' => $session, 'method' => 'GET', 'path' => '/test', 'user_agent' => 'UA', 'count' => 1]);
        Analytics::create(['application_id' => $application->id, 'session' => $session, 'method' => 'GET', 'path' => '/test2', 'user_agent' => 'UA', 'count' => 2]);

        Livewire::actingAs($user)
            ->test('applications.analytics', ['application' => $application])
            ->call('deleteSession', $session);

        $this->assertDatabaseMissing('analytics', ['session' => $session]);
    }

    public function test_does_not_delete_entries_from_other_sessions(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Analytics::create(['application_id' => $application->id, 'session' => 'target', 'method' => 'GET', 'path' => '/x', 'user_agent' => 'UA', 'count' => 1]);
        Analytics::create(['application_id' => $application->id, 'session' => 'other', 'method' => 'GET', 'path' => '/y', 'user_agent' => 'UA', 'count' => 1]);

        Livewire::actingAs($user)
            ->test('applications.analytics', ['application' => $application])
            ->call('deleteSession', 'target');

        $this->assertDatabaseHas('analytics', ['session' => 'other']);
    }

    public function test_does_not_delete_entries_from_other_applications(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();
        $otherApplication = Application::factory()->for($user)->create();

        $session = 'shared-session';
        Analytics::create(['application_id' => $application->id, 'session' => $session, 'method' => 'GET', 'path' => '/a', 'user_agent' => 'UA', 'count' => 1]);
        Analytics::create(['application_id' => $otherApplication->id, 'session' => $session, 'method' => 'GET', 'path' => '/b', 'user_agent' => 'UA', 'count' => 1]);

        Livewire::actingAs($user)
            ->test('applications.analytics', ['application' => $application])
            ->call('deleteSession', $session);

        $this->assertDatabaseHas('analytics', ['application_id' => $otherApplication->id, 'session' => $session]);
    }
}
