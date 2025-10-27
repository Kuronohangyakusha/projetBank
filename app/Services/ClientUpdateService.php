<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ClientUpdateService
{
    /**
     * Mettre à jour les informations d'un client via son compte
     */
    public function updateClientInfo(string $compteId, array $clientData): Compte
    {
        // Récupérer le compte
        $compte = Compte::findOrFail($compteId);

        // Récupérer le client associé
        $client = $compte->client;
        if (!$client) {
            throw new \Exception('Aucun client associé à ce compte');
        }

        // Récupérer l'utilisateur associé
        $user = $client->user;
        if (!$user) {
            throw new \Exception('Aucun utilisateur associé à ce client');
        }

        // Préparer les données de mise à jour
        $updateData = $this->prepareUpdateData($clientData);

        // Mettre à jour l'utilisateur si nécessaire
        if (isset($updateData['user'])) {
            $user->update($updateData['user']);
            Log::info('Utilisateur mis à jour', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($updateData['user'])
            ]);
        }

        // Mettre à jour le client si nécessaire
        if (isset($updateData['client'])) {
            $client->update($updateData['client']);
            Log::info('Client mis à jour', [
                'client_id' => $client->id,
                'updated_fields' => array_keys($updateData['client'])
            ]);
        }

        // Mettre à jour les métadonnées du compte
        $compte->update([
            'metadata->derniereModification' => now()->toISOString(),
            'metadata->version' => ($compte->metadata['version'] ?? 1) + 1,
            'updated_at' => now()
        ]);

        // Recharger le compte avec les relations
        $compte->load(['client.user']);

        Log::info('Informations client mises à jour avec succès', [
            'compte_id' => $compteId,
            'client_id' => $client->id,
            'user_id' => $user->id
        ]);

        return $compte;
    }

    /**
     * Préparer les données de mise à jour
     */
    private function prepareUpdateData(array $clientData): array
    {
        $updateData = [];

        // Données utilisateur (email, password)
        $userData = [];
        if (isset($clientData['email']) && !empty($clientData['email'])) {
            $userData['email'] = $clientData['email'];
            $userData['email_verified_at'] = now(); // Réinitialiser la vérification
        }

        if (isset($clientData['password']) && !empty($clientData['password'])) {
            $userData['password'] = Hash::make($clientData['password']);
        }

        if (!empty($userData)) {
            $updateData['user'] = $userData;
        }

        // Données client (téléphone)
        $clientUpdateData = [];
        if (isset($clientData['telephone']) && !empty($clientData['telephone'])) {
            $clientUpdateData['telephone'] = $clientData['telephone'];
        }

        if (!empty($clientUpdateData)) {
            $updateData['client'] = $clientUpdateData;
        }

        return $updateData;
    }

    /**
     * Vérifier si au moins un champ est fourni pour la mise à jour
     */
    public function validateAtLeastOneField(array $clientData): bool
    {
        $fieldsToCheck = ['telephone', 'email', 'password'];

        foreach ($fieldsToCheck as $field) {
            if (isset($clientData[$field]) && !empty($clientData[$field])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir un résumé des modifications
     */
    public function getUpdateSummary(array $oldData, array $newData): array
    {
        $changes = [];

        $fields = ['telephone', 'email'];
        foreach ($fields as $field) {
            if (isset($newData[$field]) && $oldData[$field] !== $newData[$field]) {
                $changes[$field] = [
                    'old' => $oldData[$field],
                    'new' => $newData[$field]
                ];
            }
        }

        // Pour le mot de passe, on ne log que le fait qu'il a été changé
        if (isset($newData['password'])) {
            $changes['password'] = 'Modifié';
        }

        return $changes;
    }
}