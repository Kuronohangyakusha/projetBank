<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    /**
     * Get all transactions
     */
    public function getAllTransactions(): Collection
    {
        return Transaction::with(['compte.client.user', 'compteDestination.client.user'])->get();
    }

    /**
     * Get transaction by ID
     */
    public function getTransactionById(string $id): ?Transaction
    {
        return Transaction::with(['compte.client.user', 'compteDestination.client.user'])->find($id);
    }

    /**
     * Get transactions by compte ID
     */
    public function getTransactionsByCompteId(string $compteId): Collection
    {
        return Transaction::where('compte_id', $compteId)
            ->orWhere('compte_destination_id', $compteId)
            ->with(['compte.client.user', 'compteDestination.client.user'])
            ->orderBy('date_execution', 'desc')
            ->get();
    }

    /**
     * Get transactions by client ID
     */
    public function getTransactionsByClientId(string $clientId): Collection
    {
        return Transaction::whereHas('compte', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        })->with(['compte.client.user', 'compteDestination.client.user'])
          ->orderBy('date_execution', 'desc')
          ->get();
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Update transaction
     */
    public function updateTransaction(string $id, array $data): ?Transaction
    {
        $transaction = Transaction::find($id);
        if ($transaction) {
            $transaction->update($data);
        }
        return $transaction;
    }

    /**
     * Delete transaction
     */
    public function deleteTransaction(string $id): bool
    {
        $transaction = Transaction::find($id);
        return $transaction ? $transaction->delete() : false;
    }

    /**
     * Process a deposit transaction
     */
    public function processDepot(string $compteId, float $montant, array $metadata = []): Transaction
    {
        $transaction = $this->createTransaction([
            'compte_id' => $compteId,
            'type' => 'depot',
            'montant' => $montant,
            'devise' => 'FCFA',
            'description' => 'Dépôt d\'argent',
            'statut' => 'validee',
            'date_execution' => now(),
            'metadata' => array_merge($metadata, [
                'processed_at' => now()->toISOString(),
            ]),
        ]);

        // Update compte balance
        app(CompteService::class)->updateSolde($compteId, $montant, 'add');

        return $transaction;
    }

    /**
     * Process a withdrawal transaction
     */
    public function processRetrait(string $compteId, float $montant, array $metadata = []): ?Transaction
    {
        $compteService = app(CompteService::class);

        // Check sufficient balance
        if (!$compteService->hasSufficientBalance($compteId, $montant)) {
            return null;
        }

        $transaction = $this->createTransaction([
            'compte_id' => $compteId,
            'type' => 'retrait',
            'montant' => $montant,
            'devise' => 'FCFA',
            'description' => 'Retrait d\'argent',
            'statut' => 'validee',
            'date_execution' => now(),
            'metadata' => array_merge($metadata, [
                'processed_at' => now()->toISOString(),
            ]),
        ]);

        // Update compte balance
        $compteService->updateSolde($compteId, $montant, 'subtract');

        return $transaction;
    }

    /**
     * Process a transfer transaction
     */
    public function processVirement(string $compteSourceId, string $compteDestinationId, float $montant, array $metadata = []): ?Transaction
    {
        $compteService = app(CompteService::class);

        // Check sufficient balance
        if (!$compteService->hasSufficientBalance($compteSourceId, $montant)) {
            return null;
        }

        $transaction = $this->createTransaction([
            'compte_id' => $compteSourceId,
            'type' => 'virement',
            'montant' => $montant,
            'devise' => 'FCFA',
            'description' => 'Virement bancaire',
            'compte_destination_id' => $compteDestinationId,
            'statut' => 'validee',
            'date_execution' => now(),
            'metadata' => array_merge($metadata, [
                'processed_at' => now()->toISOString(),
            ]),
        ]);

        // Update balances
        $compteService->updateSolde($compteSourceId, $montant, 'subtract');
        $compteService->updateSolde($compteDestinationId, $montant, 'add');

        return $transaction;
    }
}