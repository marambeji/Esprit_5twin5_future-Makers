<?php

namespace Database\Seeders;

use App\Models\NutritionalAnalysis;
use App\Models\Product;
use Illuminate\Database\Seeder;

class NutritionalAnalysisSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        Product::whereDoesntHave('nutritionalAnalyses')->each(function (Product $product) {
            NutritionalAnalysis::factory()->for($product)->create();
        });
    }
}
