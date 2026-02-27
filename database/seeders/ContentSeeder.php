<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Profile;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Profile::withoutGlobalScope(OwnerScope::class)->each(function (Profile $profile): void {
            Content::factory(5)
                ->sequence(fn ($sequence) => ['order' => $sequence->index + 1])
                ->create([
                    'profile_id' => $profile->id,
                    'user_id' => $profile->user_id,
                ]);
        });
    }
}
