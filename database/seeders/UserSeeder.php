<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->withoutTwoFactor()->create([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password'
        ]);
    }
}
