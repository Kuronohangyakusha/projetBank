<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;

class ClientService
{
    /**
     * Get all clients
     */
    public function getAllClients(): Collection
    {
        return Client::with('user')->get();
    }

    /**
     * Get client by ID
     */
    public function getClientById(string $id): ?Client
    {
        return Client::with(['user', 'comptes'])->find($id);
    }

    /**
     * Get clients by user ID
     */
    public function getClientsByUserId(string $userId): Collection
    {
        return Client::where('user_id', $userId)->with('comptes')->get();
    }

    /**
     * Create a new client
     */
    public function createClient(array $data): Client
    {
        return Client::create($data);
    }

    /**
     * Update client
     */
    public function updateClient(string $id, array $data): ?Client
    {
        $client = Client::find($id);
        if ($client) {
            $client->update($data);
        }
        return $client;
    }

    /**
     * Delete client
     */
    public function deleteClient(string $id): bool
    {
        $client = Client::find($id);
        return $client ? $client->delete() : false;
    }

    /**
     * Get client comptes
     */
    public function getClientComptes(string $clientId): Collection
    {
        $client = Client::find($clientId);
        return $client ? $client->comptes : collect();
    }
}