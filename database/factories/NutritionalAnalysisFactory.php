<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class NutritionalAnalysisFactory extends Factory
{
    public function definition(): array
    {
        $protein = fake()->numberBetween(1, 10);
        $carbs = fake()->numberBetween(10, 40);
        $fat = fake()->numberBetween(1, 15);

        return ['product_id' => Product::factory(), 'analysed_at' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'), 'energy_kcal' => 4 * ($protein + $carbs) + 9 * $fat, 'protein' => $protein, 'carbohydrates' => $carbs, 'sugars' => fake()->numberBetween(0, $carbs), 'fat' => $fat, 'salt' => fake()->randomFloat(2, 0, 1), 'source' => 'Jeu de données pédagogique fictif', 'notes' => 'Exemple pour tester la gestion des analyses nutritionnelles.', 'is_demo' => true];
    }
}
