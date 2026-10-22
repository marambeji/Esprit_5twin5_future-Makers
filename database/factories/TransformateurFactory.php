<?php

namespace Database\Factories;

use App\Models\Transformateur;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransformateurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom'           => fake()->randomElement(['Laiterie', 'Huilerie', 'Moulin', 'Conserverie']).' '.fake()->lastName(),
            'type_activite' => fake()->randomElement(array_keys(Transformateur::TYPES)),
            'email'         => fake()->unique()->safeEmail(),
            'telephone'     => '+216 '.fake()->numerify('7# ### ###'),
            'ville'         => fake()->randomElement(['Tunis', 'Sfax', 'Nabeul', 'Béja', 'Sousse', 'Bizerte']),
            'adresse'       => 'Zone industrielle, '.fake()->numberBetween(1, 120).' rue '.fake()->lastName(),
            'description'   => fake()->sentence(12),
        ];
    }
}
