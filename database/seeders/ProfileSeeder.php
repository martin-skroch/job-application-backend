<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
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
            'timezone' => $data['timezone'],
        ]);

        $profile = $user->profiles()->create(attributes: [
            'name' => $name,
            'email' => $email,
            'birthplace' => $data['birthplace'],
            'website' => $data['website'],
        ]);

        $skills = collect();

        foreach($data['skills'] as $order => $skill) {
            $skill = $profile->skills()->create(array_merge($skill, [
                'user_id' => $profile->user->id,
                'order' => $order,
            ]));

            $skills->push($skill->id);
        }

        foreach($data['experiences'] as $experience) {
            $experience = $profile->experiences()->create(array_merge($experience, [
                'user_id' => $profile->user->id,
            ]));

            $experience->skills()->syncWithoutDetaching($skills->shuffle()->take(4));
        }
    }
}
