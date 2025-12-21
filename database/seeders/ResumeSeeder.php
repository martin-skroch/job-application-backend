<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Skill;
use RuntimeException;
use App\Models\Resume;
use App\Models\Experience;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ResumeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('data/resume.json'));
        $data = json_decode($json, true);

        $name = $data['name'];
        $email = $data['email'];

        $user = User::factory()->withoutTwoFactor()->create([
            'name' => $name,
            'email' => $email,
        ]);

        $resume = $user->resumes()->create(attributes: [
            'name' => $name,
            'email' => $email,
            'birthplace' => $data['birthplace'],
            'website' => $data['website'],
        ]);

        $skills = collect();

        foreach($data['skills'] as $order => $skill) {
            $skill = $resume->skills()->create(array_merge($skill, [
                'user_id' => $resume->user->id,
                'order' => $order,
            ]));

            $skills->push($skill->id);
        }

        foreach($data['experiences'] as $experience) {
            $experience = $resume->experiences()->create(array_merge($experience, [
                'user_id' => $resume->user->id,
            ]));

            $experience->skills()->syncWithoutDetaching($skills->shuffle()->take(4));
        }
    }
}
