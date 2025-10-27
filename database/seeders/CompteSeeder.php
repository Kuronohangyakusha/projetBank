<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des comptes pour les clients existants
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            // Créer 1-3 comptes par client
            $nombreComptes = rand(1, 3);

            for ($i = 0; $i < $nombreComptes; $i++) {
                \App\Models\Compte::factory()->create([
                    'client_id' => $client->id,
                ]);
            }
        }

        // Créer un compte spécifique pour les tests
        $clientTest = \App\Models\Client::where('numeroCompte', 'CB00000001')->first();
        if ($clientTest) {
            \App\Models\Compte::factory()->create([
                'client_id' => $clientTest->id,
                'numeroCompte' => 'CP00000001',
                'type' => 'cheque',
                'solde' => 150000.00,
                'statut' => 'actif',
                'metadata' => [
                    'dateOuverture' => now()->toDateString(),
                    'agence' => 'Dakar Centre',
                    'fraisOuverture' => 10000.00,
                    'plafondRetrait' => 100000.00,
                    'comptePrincipal' => true,
                ],
            ]);
        }
    }
}
