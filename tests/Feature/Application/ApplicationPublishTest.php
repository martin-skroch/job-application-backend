<?php

namespace Tests\Feature\Application;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_sets_published_at_and_generates_public_id(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('publish', $application->id);

        $application->refresh();
        $this->assertNotNull($application->published_at);
        $this->assertNotEmpty($application->public_id);
        $this->assertTrue($application->isPublic());
    }

    public function test_publish_preserves_existing_public_id(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create(['public_id' => 'existingid1']);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('publish', $application->id);

        $application->refresh();
        $this->assertEquals('existingid1', $application->public_id);
    }

    public function test_publish_does_nothing_for_invalid_id(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('publish', 'not-a-ulid');

        $application->refresh();
        $this->assertNull($application->published_at);
    }

    public function test_publish_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('publish', $application->id);

        $application->refresh();
        $this->assertNull($application->published_at);
    }

    public function test_unpublish_clears_published_at(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create([
            'published_at' => now(),
            'public_id' => 'somepublicid',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('unpublish', $application->id);

        $application->refresh();
        $this->assertNull($application->published_at);
        $this->assertFalse($application->isPublic());
    }

    public function test_unpublish_does_nothing_when_application_is_not_public(): void
    {
        $user = User::factory()->create();
        $application = Application::factory()->for($user)->create([
            'published_at' => null,
            'public_id' => null,
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('unpublish', $application->id);

        $application->refresh();
        $this->assertNull($application->published_at);
    }

    public function test_unpublish_does_nothing_for_invalid_id(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('unpublish', 'not-a-ulid');

        $this->assertDatabaseEmpty('applications_history');
    }

    public function test_unpublish_cannot_be_called_for_another_users_application(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $application = Application::factory()->for($other)->create([
            'published_at' => now(),
            'public_id' => 'somepublicid',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('unpublish', $application->id);

        $application->refresh();
        $this->assertNotNull($application->published_at);
        $this->assertTrue($application->isPublic());
    }
}
