<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/ndeye-ndiaye/clients",
     *     tags={"Clients"},
     *     summary="Liste des clients",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="user_id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "epargne"}),
     *                 @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"}),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="email", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $clients = $this->clientService->getAllClients();
        return response()->json($clients);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->createClient($request->validated());
        return response()->json($client, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClientRequest $request, string $id): JsonResponse
    {
        $client = $this->clientService->updateClient($id, $request->validated());

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->clientService->deleteClient($id);

        if (!$deleted) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        return response()->json(['message' => 'Client deleted successfully']);
    }

    /**
     * Get clients by user ID
     *
     * @OA\Get(
     *     path="/api/ndeye-ndiaye/users/{userId}/clients",
     *     tags={"Clients"},
     *     summary="Clients d'un utilisateur",
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients de l'utilisateur",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="user_id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="type", type="string", enum={"cheque", "epargne"}),
     *                 @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"})
     *             )
     *         )
     *     )
     * )
     */
    public function getByUser(string $userId): JsonResponse
    {
        $clients = $this->clientService->getClientsByUserId($userId);
        return response()->json($clients);
    }

    /**
     * Get client comptes
     *
     * @OA\Get(
     *     path="/api/ndeye-ndiaye/clients/{id}/comptes",
     *     tags={"Clients"},
     *     summary="Comptes d'un client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes du client",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="client_id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CB12345678"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1000.50),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function getComptes(string $id): JsonResponse
    {
        $client = $this->clientService->getClientById($id);

        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }

        $comptes = $this->clientService->getClientComptes($id);
        return response()->json($comptes);
    }
}
