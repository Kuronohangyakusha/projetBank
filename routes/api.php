<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

/*

|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API V1 Routes
Route::prefix('ndeye-ndiaye')->group(function () {

    // User routes
    Route::apiResource('users', \App\Http\Controllers\Api\V1\UserController::class);

    // Client routes
    Route::apiResource('clients', \App\Http\Controllers\Api\V1\ClientController::class);
    Route::get('clients/{id}/comptes', [\App\Http\Controllers\Api\V1\ClientController::class, 'getComptes']);
    Route::get('users/{userId}/clients', [\App\Http\Controllers\Api\V1\ClientController::class, 'getByUser']);

    // Compte routes
    Route::apiResource('comptes', \App\Http\Controllers\Api\V1\CompteController::class);
    Route::get('comptes/numero/{numero}', [\App\Http\Controllers\Api\V1\CompteController::class, 'getByNumero']);
    Route::get('comptes/client/{clientId}', [\App\Http\Controllers\Api\V1\CompteController::class, 'getByClient']);
    Route::get('comptes/{id}/transactions', [\App\Http\Controllers\Api\V1\CompteController::class, 'getTransactions']);
    Route::patch('comptes/{id}/solde', [\App\Http\Controllers\Api\V1\CompteController::class, 'updateSolde']);

    // Transaction routes
    Route::apiResource('transactions', \App\Http\Controllers\Api\V1\TransactionController::class);
    Route::get('comptes/{compteId}/transactions', [\App\Http\Controllers\Api\V1\TransactionController::class, 'getByCompte']);
    Route::get('clients/{clientId}/transactions', [\App\Http\Controllers\Api\V1\TransactionController::class, 'getByClient']);
    Route::post('transactions/depot', [\App\Http\Controllers\Api\V1\TransactionController::class, 'depot']);
    Route::post('transactions/retrait', [\App\Http\Controllers\Api\V1\TransactionController::class, 'retrait']);
    Route::post('transactions/virement', [\App\Http\Controllers\Api\V1\TransactionController::class, 'virement']);

});

