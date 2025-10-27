<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'numeroCompte' => $this->faker->unique()->numerify('CB########'),
            'titulaire' => $this->faker->name(),
            'type' => $this->faker->randomElement(['epargne', 'cheque']),
            'solde' => $this->faker->randomFloat(2, 0, 100000),
            'devise' => 'FCFA',
            'statut' => $this->faker->randomElement(['actif', 'bloque']),
            'metadata' => [
                'dateCreation' => now()->toDateString(),
                'agence' => $this->faker->city(),
                'telephone' => $this->faker->phoneNumber(),
            ],
        ];
    }
}
