<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return ['category_id' => Category::factory(), 'name' => fake()->randomElement(['Tomates', 'Pommes', 'Huile d’olive', 'Dattes', 'Lait', 'Blé']).' '.fake()->word(), 'description' => 'Produit alimentaire de démonstration pour le catalogue NutriTrace.', 'price' => fake()->randomFloat(2, 1, 80), 'origin' => fake()->randomElement(['Béja, Tunisie', 'Nabeul, Tunisie', 'Sfax, Tunisie', 'Kébili, Tunisie'])];
    }
}
