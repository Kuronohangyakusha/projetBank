<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private TransactionService $transactionService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/api/v1/transactions",
     *     tags={"Transactions"},
     *     summary="Liste des transactions",
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="compte_id", type="string", example="uuid"),
     *                 @OA\Property(property="type", type="string", enum={"depot", "retrait", "virement"}),
     *                 @OA\Property(property="montant", type="number", format="float", example=500.00),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="description", type="string", example="Dépôt d'argent"),
     *                 @OA\Property(property="statut", type="string", example="validee"),
     *                 @OA\Property(property="date_execution", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $transactions = $this->transactionService->getAllTransactions();
        return response()->json($transactions);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $transaction = $this->transactionService->getTransactionById($id);

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $transaction = $this->transactionService->createTransaction($request->all());
        return response()->json($transaction, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $transaction = $this->transactionService->updateTransaction($id, $request->all());

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json($transaction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->transactionService->deleteTransaction($id);

        if (!$deleted) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return response()->json(['message' => 'Transaction deleted successfully']);
    }

    /**
     * Get transactions by compte ID
     */
    public function getByCompte(string $compteId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByCompteId($compteId);
        return response()->json($transactions);
    }

    /**
     * Get transactions by client ID
     */
    public function getByClient(string $clientId): JsonResponse
    {
        $transactions = $this->transactionService->getTransactionsByClientId($clientId);
        return response()->json($transactions);
    }

    /**
     * Process a deposit
     *
     * @OA\Post(
     *     path="/api/v1/transactions/depot",
     *     tags={"Transactions"},
     *     summary="Effectuer un dépôt",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id","montant"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="uuid"),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=500.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Dépôt de salaire")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dépôt effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="compte_id", type="string", example="uuid"),
     *             @OA\Property(property="type", type="string", example="depot"),
     *             @OA\Property(property="montant", type="number", format="float", example=500.00),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="description", type="string", example="Dépôt d'argent"),
     *             @OA\Property(property="statut", type="string", example="validee"),
     *             @OA\Property(property="date_execution", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function depot(Request $request): JsonResponse
    {
        $request->validate([
            'compte_id' => 'required|uuid|exists:comptes,id',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction = $this->transactionService->processDepot(
            $request->compte_id,
            $request->montant,
            ['description' => $request->description]
        );

        return response()->json($transaction, 201);
    }

    /**
     * Process a withdrawal
     *
     * @OA\Post(
     *     path="/api/v1/transactions/retrait",
     *     tags={"Transactions"},
     *     summary="Effectuer un retrait",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id","montant"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="uuid"),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=200.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Retrait DAB")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Retrait effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="compte_id", type="string", example="uuid"),
     *             @OA\Property(property="type", type="string", example="retrait"),
     *             @OA\Property(property="montant", type="number", format="float", example=200.00),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="description", type="string", example="Retrait d'argent"),
     *             @OA\Property(property="statut", type="string", example="validee"),
     *             @OA\Property(property="date_execution", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solde insuffisant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function retrait(Request $request): JsonResponse
    {
        $request->validate([
            'compte_id' => 'required|uuid|exists:comptes,id',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction = $this->transactionService->processRetrait(
            $request->compte_id,
            $request->montant,
            ['description' => $request->description]
        );

        if (!$transaction) {
            return response()->json(['message' => 'Solde insuffisant'], 400);
        }

        return response()->json($transaction, 201);
    }

    /**
     * Process a transfer
     *
     * @OA\Post(
     *     path="/api/v1/transactions/virement",
     *     tags={"Transactions"},
     *     summary="Effectuer un virement",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_source_id","compte_destination_id","montant"},
     *             @OA\Property(property="compte_source_id", type="string", format="uuid", example="uuid"),
     *             @OA\Property(property="compte_destination_id", type="string", format="uuid", example="uuid"),
     *             @OA\Property(property="montant", type="number", format="float", minimum=0.01, example=300.00),
     *             @OA\Property(property="description", type="string", maxLength=255, example="Virement vers compte épargne")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Virement effectué avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="uuid"),
     *             @OA\Property(property="compte_id", type="string", example="uuid"),
     *             @OA\Property(property="compte_destination_id", type="string", example="uuid"),
     *             @OA\Property(property="type", type="string", example="virement"),
     *             @OA\Property(property="montant", type="number", format="float", example=300.00),
     *             @OA\Property(property="devise", type="string", example="FCFA"),
     *             @OA\Property(property="description", type="string", example="Virement bancaire"),
     *             @OA\Property(property="statut", type="string", example="validee"),
     *             @OA\Property(property="date_execution", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Solde insuffisant",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Solde insuffisant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */
    public function virement(Request $request): JsonResponse
    {
        $request->validate([
            'compte_source_id' => 'required|uuid|exists:comptes,id',
            'compte_destination_id' => 'required|uuid|exists:comptes,id|different:compte_source_id',
            'montant' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $transaction = $this->transactionService->processVirement(
            $request->compte_source_id,
            $request->compte_destination_id,
            $request->montant,
            ['description' => $request->description]
        );

        if (!$transaction) {
            return response()->json(['message' => 'Solde insuffisant'], 400);
        }

        return response()->json($transaction, 201);
    }
}
