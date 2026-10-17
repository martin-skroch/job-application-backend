<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vacancy;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VacancySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::each(function (User $user): void {
            Vacancy::factory(3)->for($user)->create();
        });
    }
}
