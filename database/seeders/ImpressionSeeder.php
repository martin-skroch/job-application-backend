<?php

namespace Database\Seeders;

use App\Models\Impression;
use App\Models\Profile;
use Illuminate\Database\Seeder;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ImpressionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::withoutGlobalScope(OwnerScope::class)->each(function(Profile $profile): void {
            Impression::factory(5)->create([
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
            ]);
        });
    }
}
