<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 10 clients de test
        \App\Models\Client::factory(10)->create();

        // Créer un client spécifique pour les tests
        $user = \App\Models\User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client@test.com',
        ]);

        \App\Models\Client::factory()->create([
            'user_id' => $user->id,
            'numeroCompte' => 'CB00000001',
            'titulaire' => 'Client Test',
            'type' => 'cheque',
            'solde' => 50000.00,
            'devise' => 'FCFA',
            'statut' => 'actif',
            'metadata' => [
                'dateCreation' => now()->toDateString(),
                'agence' => 'Dakar Centre',
                'telephone' => '+221 77 123 45 67',
                'profession' => 'Développeur',
            ],
        ]);
    }
}
