<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['depot', 'retrait', 'virement']);
        $compte = \App\Models\Compte::factory()->create();

        $compteDestination = null;
        if ($type === 'virement') {
            $compteDestination = \App\Models\Compte::factory()->create();
        }

        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'compte_id' => $compte->id,
            'type' => $type,
            'montant' => $this->faker->randomFloat(2, 1000, 50000),
            'devise' => 'FCFA',
            'description' => $this->faker->sentence(),
            'compte_destination_id' => $compteDestination?->id,
            'statut' => $this->faker->randomElement(['en_attente', 'validee', 'rejete']),
            'date_execution' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'metadata' => [
                'canal' => $this->faker->randomElement(['guichet', 'mobile', 'web', 'api']),
                'agent' => $this->faker->name(),
                'reference' => $this->faker->unique()->numerify('TXN########'),
                'frais' => $this->faker->randomFloat(2, 0, 500),
            ],
        ];
    }
}
