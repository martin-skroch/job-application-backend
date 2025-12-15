<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->withoutTwoFactor()->create([
            'name' => 'Martin',
            'email' => 'moin@martin-skroch.de',
        ]);

        $resume = $user->resumes()->create([
            'name' => 'Martin Skroch',
            'image' => null,
            'address' => null,
            'post_code' => null,
            'location' => null,
            'birthdate' => null,
            'birthplace' => 'Neubrandenburg',
            'phone' => null,
            'email' => 'moin@martin-skroch.de',
            'website' => 'https://martin-skroch.de',
        ]);

        // Resume::factory(2)
        //     ->has(Experience::factory()->count(10))
        //     ->create()
        //     ->each(function (Resume $resume) {
        //         foreach (Skill::factory(10)->create() as $index => $skill) {
        //             $resume->skills()->attach($skill->id, [
        //                 'order' => $index + 1,
        //             ]);
        //         }
        //     })
        // ;
    }
}
