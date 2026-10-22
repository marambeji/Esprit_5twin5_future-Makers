<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProducteurFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom'       => fake()->lastName(),
            'prenom'    => fake()->firstName(),
            'email'     => fake()->unique()->safeEmail(),
            'telephone' => fake()->phoneNumber(),
            'adresse'   => fake()->address(),
        ];
    }
}
