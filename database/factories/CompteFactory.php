<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'client_id' => \App\Models\Client::factory(),
            'numeroCompte' => $this->faker->unique()->numerify('CP########'),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 100000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['actif', 'bloque']),
            'metadata' => [
                'dateOuverture' => now()->toDateString(),
                'agence' => $this->faker->city(),
                'fraisOuverture' => $this->faker->randomFloat(2, 5000, 15000),
                'plafondRetrait' => $this->faker->randomElement([50000, 100000, 200000]),
            ],
        ];
    }
}
