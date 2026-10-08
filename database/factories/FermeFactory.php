<?php

namespace Database\Factories;

use App\Models\Producteur;
use Illuminate\Database\Eloquent\Factories\Factory;

class FermeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producteur_id' => Producteur::factory(),
            'nom'           => fake()->randomElement(['Ferme', 'Exploitation', 'Domaine']).' '.fake()->lastName(),
            'localisation'  => fake()->randomElement(['Béja, Tunisie', 'Nabeul, Tunisie', 'Sfax, Tunisie', 'Kébili, Tunisie', 'Zaghouan, Tunisie', 'Siliana, Tunisie', 'Jendouba, Tunisie']),
            'superficie'    => fake()->randomFloat(2, 1, 500),
            'description'   => fake()->sentence(10),
        ];
    }
}
