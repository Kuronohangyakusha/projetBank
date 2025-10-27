<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des transactions pour les comptes existants
        $comptes = \App\Models\Compte::all();

        foreach ($comptes as $compte) {
            // Créer 3-8 transactions par compte
            $nombreTransactions = rand(3, 8);

            for ($i = 0; $i < $nombreTransactions; $i++) {
                \App\Models\Transaction::factory()->create([
                    'compte_id' => $compte->id,
                ]);
            }
        }

        // Créer des transactions spécifiques pour le compte de test
        $compteTest = \App\Models\Compte::where('numeroCompte', 'CP00000001')->first();
        if ($compteTest) {
            // Dépôt initial
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteTest->id,
                'type' => 'depot',
                'montant' => 200000.00,
                'description' => 'Dépôt initial',
                'statut' => 'validee',
                'date_execution' => now()->subDays(30),
                'metadata' => [
                    'canal' => 'guichet',
                    'agent' => 'Agent Principal',
                    'reference' => 'DEP001',
                    'frais' => 0,
                ],
            ]);

            // Quelques retraits
            \App\Models\Transaction::factory()->create([
                'compte_id' => $compteTest->id,
                'type' => 'retrait',
                'montant' => 50000.00,
                'description' => 'Retrait DAB',
                'statut' => 'validee',
                'date_execution' => now()->subDays(15),
                'metadata' => [
                    'canal' => 'dab',
                    'agent' => 'DAB Centre Ville',
                    'reference' => 'RET001',
                    'frais' => 500,
                ],
            ]);

            // Un virement
            $autreCompte = \App\Models\Compte::where('id', '!=', $compteTest->id)->first();
            if ($autreCompte) {
                \App\Models\Transaction::factory()->create([
                    'compte_id' => $compteTest->id,
                    'type' => 'virement',
                    'montant' => 25000.00,
                    'description' => 'Virement vers compte secondaire',
                    'compte_destination_id' => $autreCompte->id,
                    'statut' => 'validee',
                    'date_execution' => now()->subDays(7),
                    'metadata' => [
                        'canal' => 'web',
                        'agent' => 'Application Web',
                        'reference' => 'VIR001',
                        'frais' => 250,
                    ],
                ]);
            }
        }
    }
}
