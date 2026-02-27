<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileContentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_contents_are_visible_on_page(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->assertSee($content->heading);
    }

    public function test_inactive_contents_are_visible_on_page(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->inactive()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->assertSee($content->heading);
    }

    public function test_create_form_is_opened_with_default_values(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open')
            ->assertSet('isEditing', false)
            ->assertSet('order', 1);
    }

    public function test_create_form_sets_next_order_number(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        Content::factory()->for($profile)->for($user)->active()->create(['order' => 3]);

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open')
            ->assertSet('order', 4);
    }

    public function test_content_can_be_created(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open')
            ->set('heading', 'Wer bin ich?')
            ->set('name', 'wer-bin-ich')
            ->set('text', 'Some text about me.')
            ->set('active', true)
            ->set('order', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contents', [
            'profile_id' => $profile->id,
            'heading' => 'Wer bin ich?',
            'name' => 'wer-bin-ich',
            'active' => true,
        ]);
    }

    public function test_edit_form_is_pre_filled_when_editing(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open', $content->id)
            ->assertSet('isEditing', true)
            ->assertSet('contentId', $content->id)
            ->assertSet('heading', $content->heading)
            ->assertSet('name', $content->name)
            ->assertSet('text', $content->text)
            ->assertSet('order', $content->order);
    }

    public function test_content_can_be_edited(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open', $content->id)
            ->set('heading', 'Updated heading')
            ->set('name', 'updated-heading')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'heading' => 'Updated heading',
            'name' => 'updated-heading',
        ]);
    }

    public function test_content_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('delete', $content->id);

        $this->assertDatabaseMissing('contents', ['id' => $content->id]);
    }

    public function test_delete_with_invalid_id_does_nothing(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('delete', 'not-a-valid-ulid')
            ->assertHasNoErrors();
    }

    public function test_active_content_can_be_deactivated(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('toggleActive', $content->id, true);

        $this->assertDatabaseHas('contents', ['id' => $content->id, 'active' => false]);
    }

    public function test_inactive_content_can_be_activated(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->inactive()->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('toggleActive', $content->id, false);

        $this->assertDatabaseHas('contents', ['id' => $content->id, 'active' => true]);
    }

    public function test_content_order_can_be_updated(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $first = Content::factory()->for($profile)->for($user)->active()->create(['order' => 1]);
        $second = Content::factory()->for($profile)->for($user)->active()->create(['order' => 2]);

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('updateOrder', [
                ['id' => $first->id, 'order' => 2],
                ['id' => $second->id, 'order' => 1],
            ]);

        $this->assertDatabaseHas('contents', ['id' => $first->id, 'order' => 2]);
        $this->assertDatabaseHas('contents', ['id' => $second->id, 'order' => 1]);
    }

    public function test_name_is_auto_filled_from_heading(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open')
            ->set('heading', 'Meine Erfahrung')
            ->assertSet('name', 'meine-erfahrung');
    }

    public function test_name_is_not_auto_filled_when_editing(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $content = Content::factory()->for($profile)->for($user)->active()->create(['name' => 'original-name']);

        Livewire::actingAs($user)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->call('open', $content->id)
            ->set('heading', 'Changed heading')
            ->assertSet('name', 'original-name');
    }

    public function test_user_cannot_view_another_users_profile_contents(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $profile = Profile::factory()->for($owner)->create();

        Livewire::actingAs($other)
            ->test('pages::profiles.contents', ['profile' => $profile])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_content_from_another_users_profile(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $ownerProfile = Profile::factory()->for($owner)->create();
        $otherProfile = Profile::factory()->for($other)->create();
        $content = Content::factory()->for($ownerProfile)->for($owner)->active()->create();

        Livewire::actingAs($other)
            ->test('pages::profiles.contents', ['profile' => $otherProfile])
            ->call('delete', $content->id);

        $this->assertDatabaseHas('contents', ['id' => $content->id]);
    }
}
