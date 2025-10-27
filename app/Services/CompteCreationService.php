<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CompteCreationService
{
    /**
     * Créer un nouveau compte avec génération automatique
     */
    public function createCompte(array $data): Compte
    {
        // Vérifier ou créer le client
        $client = $this->findOrCreateClient($data['client']);

        // Générer le numéro de compte
        $numeroCompte = $this->generateNumeroCompte($data['type']);

        // Créer le compte
        $compte = Compte::create([
            'client_id' => $client->id,
            'numeroCompte' => $numeroCompte,
            'type' => $data['type'],
            'solde' => $data['soldeInitial'],
            'devise' => $data['devise'],
            'statut' => 'actif',
            'metadata' => [
                'dateCreation' => now()->toDateString(),
                'agence' => 'Dakar Centre',
                'fraisOuverture' => $this->calculateFraisOuverture($data['type']),
                'plafondRetrait' => $this->calculatePlafondRetrait($data['type']),
                'comptePrincipal' => $this->isComptePrincipal($client->id),
            ]
        ]);

        // Envoyer les notifications
        $this->sendNotifications($client, $compte);

        return $compte;
    }

    /**
     * Trouver ou créer un client
     */
    private function findOrCreateClient(array $clientData): Client
    {
        if (isset($clientData['id']) && !empty($clientData['id'])) {
            return Client::findOrFail($clientData['id']);
        }

        // Créer l'utilisateur d'abord
        $user = User::create([
            'name' => $clientData['titulaire'],
            'email' => $clientData['email'],
            'password' => Hash::make($this->generatePassword()),
            'email_verified_at' => now(),
        ]);

        // Générer un code d'authentification
        $authCode = $this->generateAuthCode();

        // Créer le client
        $client = Client::create([
            'user_id' => $user->id,
            'numeroCompte' => $this->generateNumeroClient(),
            'titulaire' => $clientData['titulaire'],
            'type' => 'cheque', // Par défaut
            'solde' => 0, // Le solde sera sur le compte créé
            'devise' => 'FCFA',
            'statut' => 'actif',
            'telephone' => $clientData['telephone'],
            'metadata' => [
                'dateCreation' => now()->toDateString(),
                'agence' => 'Dakar Centre',
                'authCode' => $authCode,
                'adresse' => $clientData['adresse'] ?? null,
                'profession' => $clientData['profession'] ?? null,
            ]
        ]);

        return $client;
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(string $type): string
    {
        $prefix = $type === 'cheque' ? 'CB' : 'CE';
        $timestamp = now()->format('ymdHis');
        $random = strtoupper(Str::random(4));

        $numero = $prefix . $timestamp . $random;

        // Vérifier l'unicité
        while (Compte::where('numeroCompte', $numero)->exists()) {
            $random = strtoupper(Str::random(4));
            $numero = $prefix . $timestamp . $random;
        }

        return $numero;
    }

    /**
     * Générer un numéro de client unique
     */
    private function generateNumeroClient(): string
    {
        $timestamp = now()->format('ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $numero = 'CL' . $timestamp . $random;

        // Vérifier l'unicité
        while (Client::where('numeroCompte', $numero)->exists()) {
            $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $numero = 'CL' . $timestamp . $random;
        }

        return $numero;
    }

    /**
     * Générer un mot de passe sécurisé
     */
    private function generatePassword(): string
    {
        return Str::random(12) . rand(100, 999);
    }

    /**
     * Générer un code d'authentification
     */
    private function generateAuthCode(): string
    {
        return str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculer les frais d'ouverture
     */
    private function calculateFraisOuverture(string $type): int
    {
        return $type === 'cheque' ? 10000 : 5000;
    }

    /**
     * Calculer le plafond de retrait
     */
    private function calculatePlafondRetrait(string $type): int
    {
        return $type === 'cheque' ? 100000 : 50000;
    }

    /**
     * Vérifier si c'est le compte principal du client
     */
    private function isComptePrincipal(string $clientId): bool
    {
        return Compte::where('client_id', $clientId)->count() === 0;
    }

    /**
     * Envoyer les notifications (email et SMS)
     */
    private function sendNotifications(Client $client, Compte $compte): void
    {
        try {
            // Envoyer l'email d'authentification
            $this->sendAuthEmail($client, $compte);

            // Envoyer le SMS avec le code
            $this->sendAuthSMS($client);

            Log::info('Notifications envoyées pour le nouveau compte', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'numero_compte' => $compte->numeroCompte
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications', [
                'client_id' => $client->id,
                'compte_id' => $compte->id,
                'error' => $e->getMessage()
            ]);

            // Ne pas bloquer la création du compte si les notifications échouent
        }
    }

    /**
     * Envoyer l'email d'authentification
     */
    private function sendAuthEmail(Client $client, Compte $compte): void
    {
        // Simulation d'envoi d'email (à remplacer par un vrai service d'email)
        Log::info('Email d\'authentification envoyé', [
            'to' => $client->user->email,
            'subject' => 'Bienvenue - Vos identifiants de connexion',
            'numero_compte' => $compte->numeroCompte,
            'password' => 'GÉNÉRÉ_AUTOMATIQUEMENT' // En production, utiliser un service de notification sécurisé
        ]);

        // TODO: Implémenter l'envoi réel d'email avec Laravel Mail
        // Mail::to($client->user->email)->send(new WelcomeEmail($client, $compte));
    }

    /**
     * Envoyer le SMS avec le code d'authentification
     */
    private function sendAuthSMS(Client $client): void
    {
        $authCode = $client->metadata['authCode'] ?? null;

        if (!$authCode) {
            Log::warning('Code d\'authentification manquant pour le SMS', [
                'client_id' => $client->id
            ]);
            return;
        }

        // Simulation d'envoi de SMS (à remplacer par un vrai service SMS)
        Log::info('SMS d\'authentification envoyé', [
            'to' => $client->telephone,
            'message' => "Votre code d'authentification Banque: {$authCode}",
            'auth_code' => $authCode
        ]);

        // TODO: Implémenter l'envoi réel de SMS
        // $smsService->send($client->telephone, "Votre code: {$authCode}");
    }
}