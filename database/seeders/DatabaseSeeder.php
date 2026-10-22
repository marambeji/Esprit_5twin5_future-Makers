<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([CatalogSeeder::class, UserSeeder::class, DistributionSeeder::class, ProducteurSeeder::class, LabelSeeder::class, NutritionalAnalysisSeeder::class, TransformationSeeder::class]);
    }
}
