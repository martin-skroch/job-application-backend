<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileCoverLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_letter_is_stored_on_profile(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['cover_letter' => null, 'image' => null]);

        Livewire::actingAs($user)
            ->test('pages::profiles.show', ['profile' => $profile, 'user' => $user])
            ->set('cover_letter', 'Dear Sir or Madam, I am writing to apply...')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'cover_letter' => 'Dear Sir or Madam, I am writing to apply...',
        ]);
    }

    public function test_cover_letter_can_be_null(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create(['cover_letter' => null]);

        $this->assertNull($profile->cover_letter);
    }

    public function test_cover_letter_is_pre_populated_when_creating_application(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'cover_letter' => 'My default cover letter text.',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('profile_id', $profile->id)
            ->assertSet('text', 'My default cover letter text.');
    }

    public function test_cover_letter_overwrites_existing_text_when_profile_changes(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'cover_letter' => 'My default cover letter text.',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->set('text', 'Custom text already written.')
            ->set('profile_id', $profile->id)
            ->assertSet('text', 'My default cover letter text.');
    }

    public function test_cover_letter_is_not_pre_populated_when_editing_existing_application(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'cover_letter' => 'My default cover letter text.',
        ]);
        $application = \App\Models\Application::factory()->for($user)->for($profile)->create([
            'text' => 'Existing application text.',
        ]);

        Livewire::actingAs($user)
            ->test('pages::applications.index')
            ->call('open', $application->id)
            ->set('profile_id', $profile->id)
            ->assertSet('text', 'Existing application text.');
    }
}
