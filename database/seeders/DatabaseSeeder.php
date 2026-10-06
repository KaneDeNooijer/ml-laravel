<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Creates a useful local dataset for development, tests, and demonstrations. */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(5)->create();
        Meal::factory()->count(3)->create();

    }
}
