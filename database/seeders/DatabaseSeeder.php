<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            NewsSeeder::class,
            EventSeeder::class,
            ListingSeeder::class,
            ProfessionalCategorySeeder::class,
            DemoSeeder::class,
        ]);
    }
}
