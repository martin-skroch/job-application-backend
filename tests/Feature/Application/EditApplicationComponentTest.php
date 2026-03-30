<?php

namespace Tests\Feature\Application;

use App\Enum\FormOfAddress;
use App\Enum\SalaryBehaviors;
use App\Models\Application;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditApplicationComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_earliest_entry_date_can_be_set_to_null(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'earliest_entry_date' => '2025-06-01',
            'form_of_address' => FormOfAddress::Formal,
            'salary_behavior' => SalaryBehaviors::Hidden,
        ]);

        Livewire::actingAs($user)
            ->test('applications.edit-application', ['application' => $application])
            ->set('earliest_entry_date', '')
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'earliest_entry_date' => null,
        ]);
    }

    public function test_clear_button_sets_earliest_entry_date_to_null(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'earliest_entry_date' => '2025-06-01',
            'form_of_address' => FormOfAddress::Formal,
            'salary_behavior' => SalaryBehaviors::Hidden,
        ]);

        Livewire::actingAs($user)
            ->test('applications.edit-application', ['application' => $application])
            ->call('$set', 'earliest_entry_date', null)
            ->assertSet('earliest_entry_date', null)
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'earliest_entry_date' => null,
        ]);
    }

    public function test_earliest_entry_date_can_be_set(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create();
        $application = Application::factory()->for($user)->for($profile)->create([
            'earliest_entry_date' => null,
            'form_of_address' => FormOfAddress::Formal,
            'salary_behavior' => SalaryBehaviors::Hidden,
        ]);

        Livewire::actingAs($user)
            ->test('applications.edit-application', ['application' => $application])
            ->set('earliest_entry_date', '2025-09-01')
            ->call('save');

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'earliest_entry_date' => '2025-09-01',
        ]);
    }
}
