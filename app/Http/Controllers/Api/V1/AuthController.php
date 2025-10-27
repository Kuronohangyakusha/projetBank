<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;

/**
 * @OA\Info(
 *     title="ProjetBank API",
 *     version="1.0.0",
 *     description="API de gestion bancaire"
 * )
 *
 * @OA\Server(
 *     url="https://projetbank-4.onrender.com/api/ndeye-ndiaye",
 *     description="Serveur de production"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ndeye-ndiaye/users",
     *     summary="Liste des utilisateurs",
     *     tags={"Utilisateurs"},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Test User"),
     *                 @OA\Property(property="email", type="string", format="email", example="test@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function getUsers()
    {
        $users = User::all();
        return response()->json($users);
    }
}