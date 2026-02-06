<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Profile;
use App\Models\Scopes\OwnerScope;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::withoutGlobalScope(OwnerScope::class)->each(function(Profile $profile): void {
            Application::factory(5)->create([
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
            ]);
        });
    }
}
