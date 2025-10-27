<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompteRequest;
use App\Services\CompteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompteController extends Controller
{
    public function __construct(
        private CompteService $compteService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/comptes",
     *     tags={"Comptes"},
     *     summary="Liste des comptes avec pagination et filtres",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par titulaire ou numéro",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde", "titulaire"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="client_id", type="string", example="uuid"),
     *                     @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *                     @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *                     @OA\Property(property="devise", type="string", example="FCFA"),
     *                     @OA\Property(property="statut", type="string", enum={"actif", "bloque"}),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="client", type="object",
     *                         @OA\Property(property="id", type="string"),
     *                         @OA\Property(property="titulaire", type="string"),
     *                         @OA\Property(property="user", type="object",
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="email", type="string")
     *                         )
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=3),
     *                 @OA\Property(property="totalItems", type="integer", example=25),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=true),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="self", type="string", example="/api/v1/comptes?page=1&limit=10"),
     *                 @OA\Property(property="next", type="string", example="/api/v1/comptes?page=2&limit=10"),
     *                 @OA\Property(property="first", type="string", example="/api/v1/comptes?page=1&limit=10"),
     *                 @OA\Property(property="last", type="string", example="/api/v1/comptes?page=3&limit=10")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['page', 'limit', 'type', 'statut', 'search', 'sort', 'order']);
        $perPage = $request->get('limit', 10);

        return $this->compteService->getAllComptes($filters, $perPage);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/comptes/{compteId}",
     *     tags={"Comptes"},
     *     summary="Récupérer un compte spécifique",
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte trouvé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"}, example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", example="Inactivité de 30+ jours"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="compteId", type="string", example="550e8400-e29b-41d4-a716-446655440000")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $compte = $this->compteService->getCompteById($id);

            if (!$compte) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'COMPTE_NOT_FOUND',
                        'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                        'details' => [
                            'compteId' => $id
                        ]
                    ]
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new \App\Http\Resources\CompteResource($compte)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Une erreur interne est survenue'
                ]
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/comptes",
     *     tags={"Comptes"},
     *     summary="Créer un nouveau compte bancaire",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","soldeInitial","devise","client"},
     *             @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *             @OA\Property(property="soldeInitial", type="number", format="float", minimum=10000, example=500000),
     *             @OA\Property(property="devise", type="string", enum={"FCFA", "XOF", "EUR", "USD"}, example="FCFA"),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="id", type="string", format="uuid", description="ID du client existant (optionnel)"),
     *                 @OA\Property(property="titulaire", type="string", description="Nom du titulaire (requis si nouveau client)", example="Hawa BB Wane"),
     *                 @OA\Property(property="email", type="string", format="email", description="Email (requis si nouveau client)", example="cheikh.sy@example.com"),
     *                 @OA\Property(property="telephone", type="string", description="Téléphone sénégalais (requis si nouveau client)", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", description="Adresse (optionnel)", example="Dakar, Sénégal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="660f9511-f30c-52e5-b827-557766551111"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123460"),
     *                 @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *                 @OA\Property(property="solde", type="number", format="float", example=500000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"}, example="actif"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="titulaire", type="string", example="Le nom du titulaire est requis"),
     *                     @OA\Property(property="soldeInitial", type="string", example="Le solde initial doit être supérieur à 0")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function store(\App\Http\Requests\CreateCompteRequest $request)
    {
        try {
            $compteCreationService = app(\App\Services\CompteCreationService::class);
            $compte = $compteCreationService->createCompte($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => new \App\Http\Resources\CompteResource($compte)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors()
                ]
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Une erreur interne est survenue'
                ]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Patch(
     *     path="/comptes/{compteId}",
     *     tags={"Comptes"},
     *     summary="Mettre à jour les informations du client associé à un compte",
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"informationsClient"},
     *             @OA\Property(property="informationsClient", type="object",
     *                 @OA\Property(property="telephone", type="string", description="Nouveau numéro de téléphone sénégalais", example="771234568"),
     *                 @OA\Property(property="email", type="string", format="email", description="Nouvel email", example="nouveau.email@example.com"),
     *                 @OA\Property(property="password", type="string", description="Nouveau mot de passe (10+ chars, majuscule, 2 minuscules, 2 spéciaux)", example="Password123!@")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations client mises à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *                 @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"}, example="bloque"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="derniereModification", type="string", format="date-time"),
     *                     @OA\Property(property="version", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                 @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                 @OA\Property(property="details", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="COMPTE_NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Le compte avec l'ID spécifié n'existe pas")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function update(\App\Http\Requests\UpdateClientRequest $request, string $compteId)
    {
        try {
            $clientUpdateService = app(\App\Services\ClientUpdateService::class);
            $compte = $clientUpdateService->updateClientInfo($compteId, $request->input('informationsClient', []));

            return response()->json([
                'success' => true,
                'message' => 'Compte mis à jour avec succès',
                'data' => new \App\Http\Resources\CompteResource($compte)
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Les données fournies sont invalides',
                    'details' => $e->errors()
                ]
            ], 400);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'COMPTE_NOT_FOUND',
                    'message' => 'Le compte avec l\'ID spécifié n\'existe pas',
                    'details' => [
                        'compteId' => $compteId
                    ]
                ]
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Une erreur interne est survenue'
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->compteService->deleteCompte($id);

        if (!$deleted) {
            return response()->json(['message' => 'Compte not found'], 404);
        }

        return response()->json(['message' => 'Compte deleted successfully']);
    }

    /**
     * Get compte by numero
     *
     * @OA\Get(
     *     path="/comptes/numero/{numero}",
     *     tags={"Comptes"},
     *     summary="Rechercher un compte par numéro",
     *     @OA\Parameter(
     *         name="numero",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="client_id", type="string", example="uuid"),
     *             @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *             @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque"}),
     *             @OA\Property(property="client", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="titulaire", type="string"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function getByNumero(string $numero): JsonResponse
    {
        $compte = $this->compteService->getCompteByNumero($numero);

        if (!$compte) {
            return response()->json(['message' => 'Compte not found'], 404);
        }

        return response()->json($compte);
    }

    /**
     * Get comptes by client ID
     *
     * @OA\Get(
     *     path="/comptes/client/{clientId}",
     *     tags={"Comptes"},
     *     summary="Comptes d'un client avec pagination et filtres",
     *     @OA\Parameter(
     *         name="clientId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", default=1, minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrer par type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Filtrer par statut",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"dateCreation", "solde"}, default="dateCreation")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes du client récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="string", example="uuid"),
     *                     @OA\Property(property="client_id", type="string", example="uuid"),
     *                     @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *                     @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *                     @OA\Property(property="devise", type="string", example="FCFA"),
     *                     @OA\Property(property="statut", type="string", enum={"actif", "bloque"})
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="currentPage", type="integer", example=1),
     *                 @OA\Property(property="totalPages", type="integer", example=1),
     *                 @OA\Property(property="totalItems", type="integer", example=2),
     *                 @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                 @OA\Property(property="hasNext", type="boolean", example=false),
     *                 @OA\Property(property="hasPrevious", type="boolean", example=false)
     *             ),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="self", type="string", example="/api/v1/comptes/client/uuid?page=1&limit=10"),
     *                 @OA\Property(property="next", type="string", example=null),
     *                 @OA\Property(property="first", type="string", example="/api/v1/comptes/client/uuid?page=1&limit=10"),
     *                 @OA\Property(property="last", type="string", example="/api/v1/comptes/client/uuid?page=1&limit=10")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function getByClient(Request $request, string $clientId)
    {
        // Vérifier que le client existe
        $client = \App\Models\Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $filters = $request->only(['page', 'limit', 'type', 'statut', 'search', 'sort', 'order']);
        $perPage = $request->get('limit', 10);

        return $this->compteService->getComptesByClientId($clientId, $filters, $perPage);
    }

    /**
     * Get compte transactions
     */
    public function getTransactions(string $id): JsonResponse
    {
        $compte = $this->compteService->getCompteById($id);

        if (!$compte) {
            return response()->json(['message' => 'Compte not found'], 404);
        }

        $transactions = $this->compteService->getCompteTransactions($id);
        return response()->json($transactions);
    }

    /**
     * Update compte solde
     *
     * @OA\Patch(
     *     path="/comptes/{id}/solde",
     *     tags={"Comptes"},
     *     summary="Mettre à jour le solde d'un compte",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant","operation"},
     *             @OA\Property(property="montant", type="number", format="float", example=500.00),
     *             @OA\Property(property="operation", type="string", enum={"add", "subtract", "set"}, example="add")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solde mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *             @OA\Property(property="solde", type="number", format="float", example=1500.50),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé"
     *     )
     * )
     */
    public function updateSolde(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'montant' => 'required|numeric|min:0',
            'operation' => 'required|in:add,subtract,set',
        ]);

        $success = $this->compteService->updateSolde(
            $id,
            $request->montant,
            $request->operation
        );

        if (!$success) {
            return response()->json(['message' => 'Compte not found'], 404);
        }

        $compte = $this->compteService->getCompteById($id);
        return response()->json($compte);
    }
}
