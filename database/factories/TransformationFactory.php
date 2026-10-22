<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transformateur;
use App\Models\Transformation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransformationFactory extends Factory
{
    public function definition(): array
    {
        $procede = fake()->randomElement(array_keys(Transformation::PROCEDES));
        $quantite = fake()->randomFloat(2, 50, 2000);

        return [
            'transformateur_id'   => Transformateur::factory(),
            'product_id'          => Product::factory(),
            'procede'             => $procede,
            'date_transformation' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'quantite_kg'         => $quantite,
        ] + Transformation::estimationLocale($procede, $quantite);
    }
}
