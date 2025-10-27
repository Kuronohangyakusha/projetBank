<?php

namespace App\Services;

use App\Models\Compte;
use Illuminate\Database\Eloquent\Collection;

class CompteService
{
    /**
     * Get all comptes with filters and pagination
     */
    public function getAllComptes(array $filters = [], int $perPage = 10): \App\Http\Resources\CompteCollection
    {
        $query = Compte::with(['client.user']);

        // Apply filters
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numeroCompte', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('titulaire', 'like', "%{$search}%");
                  });
            });
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'desc';

        // Map sort fields
        $sortFields = [
            'dateCreation' => 'created_at',
            'solde' => 'solde',
            'titulaire' => 'client.titulaire'
        ];

        if (array_key_exists($sortBy, $sortFields)) {
            if ($sortBy === 'titulaire') {
                $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                      ->orderBy('clients.titulaire', $sortOrder)
                      ->select('comptes.*');
            } else {
                $query->orderBy($sortFields[$sortBy], $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate
        $page = $filters['page'] ?? 1;
        $perPage = min($perPage, 100); // Max 100 items per page

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return new \App\Http\Resources\CompteCollection($paginated);
    }

    /**
     * Get compte by ID
     */
    public function getCompteById(string $id): ?Compte
    {
        return Compte::with(['client.user', 'transactions'])->find($id);
    }

    /**
     * Get comptes by client ID with filters and pagination
     */
    public function getComptesByClientId(string $clientId, array $filters = [], int $perPage = 10): \App\Http\Resources\CompteCollection
    {
        $query = Compte::where('client_id', $clientId)->with(['client.user']);

        // Apply filters
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['statut']) && !empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('numeroCompte', 'like', "%{$search}%");
        }

        // Apply sorting
        $sortBy = $filters['sort'] ?? 'created_at';
        $sortOrder = $filters['order'] ?? 'desc';

        // Map sort fields
        $sortFields = [
            'dateCreation' => 'created_at',
            'solde' => 'solde',
            'titulaire' => 'client.titulaire'
        ];

        if (array_key_exists($sortBy, $sortFields)) {
            if ($sortBy === 'titulaire') {
                $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                      ->orderBy('clients.titulaire', $sortOrder)
                      ->select('comptes.*');
            } else {
                $query->orderBy($sortFields[$sortBy], $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Paginate
        $page = $filters['page'] ?? 1;
        $perPage = min($perPage, 100); // Max 100 items per page

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return new \App\Http\Resources\CompteCollection($paginated);
    }

    /**
     * Get comptes by numero
     */
    public function getCompteByNumero(string $numero): ?Compte
    {
        return Compte::where('numeroCompte', $numero)->with(['client.user', 'transactions'])->first();
    }

    /**
     * Create a new compte
     */
    public function createCompte(array $data): Compte
    {
        return Compte::create($data);
    }

    /**
     * Update compte
     */
    public function updateCompte(string $id, array $data): ?Compte
    {
        $compte = Compte::find($id);
        if ($compte) {
            $compte->update($data);
        }
        return $compte;
    }

    /**
     * Delete compte
     */
    public function deleteCompte(string $id): bool
    {
        $compte = Compte::find($id);
        return $compte ? $compte->delete() : false;
    }

    /**
     * Get compte transactions
     */
    public function getCompteTransactions(string $compteId): Collection
    {
        $compte = Compte::find($compteId);
        return $compte ? $compte->transactions : collect();
    }

    /**
     * Update compte solde
     */
    public function updateSolde(string $compteId, float $montant, string $operation = 'add'): bool
    {
        $compte = Compte::find($compteId);
        if (!$compte) {
            return false;
        }

        if ($operation === 'add') {
            $compte->increment('solde', $montant);
        } elseif ($operation === 'subtract') {
            $compte->decrement('solde', $montant);
        } elseif ($operation === 'set') {
            $compte->solde = $montant;
            $compte->save();
        }

        return true;
    }

    /**
     * Check if compte has sufficient balance
     */
    public function hasSufficientBalance(string $compteId, float $montant): bool
    {
        $compte = Compte::find($compteId);
        return $compte && $compte->solde >= $montant;
    }
}