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
        $name = 'Martin Skroch';
        $email = 'moin@martin-skroch.de';

        $user = User::factory()->withoutTwoFactor()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $resume = $user->resumes()->create([
            'name' => $name,
            'image' => null,
            'address' => null,
            'post_code' => null,
            'location' => null,
            'birthdate' => null,
            'birthplace' => 'Neubrandenburg',
            'phone' => null,
            'email' => $email,
            'website' => 'https://martin-skroch.de',
        ]);

        $resume->experiences()->create([
            'user_id' => $user->id,
            'position' => 'Full Stack Web Developer',
            'location' => 'Ilmenau (Remote)',
            'entry' => '2025-08-01',
            'exit' => null,
            'active' => true,
        ]);

        $resume->experiences()->create([
            'user_id' => $user->id,
            'position' => 'Full Stack Web Developer',
            'location' => 'Neubrandenburg',
            'entry' => '2024-08-01',
            'exit' => '2025-07-31',
            'active' => true,
        ]);

        $resume->experiences()->create([
            'user_id' => $user->id,
            'position' => 'Full Stack Web Developer',
            'location' => 'Neubrandenburg (Hybrid)',
            'entry' => '2015-10-01',
            'exit' => '2022-10-31',
            'active' => true,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'PHP',
            'rating' => 5,
            'order' => 1,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'JavaScript',
            'rating' => 5,
            'order' => 2,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'Dart',
            'info' => 'Flutter',
            'rating' => 3,
            'order' => 3,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'MySQL',
            'rating' => 3,
            'order' => 4,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'HTML',
            'rating' => 6,
            'order' => 5,
        ]);

        $resume->skills()->create([
            'user_id' => $user->id,
            'name' => 'CSS',
            'info' => 'PostCSS, SASS, LESS',
            'rating' => 6,
            'order' => 6,
        ]);
    }
}
