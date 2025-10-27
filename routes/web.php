<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Bienvenue sur l\'API ProjetBank',
        'version' => '1.0.0',
        'documentation' => url('/ndiaye/documentation'),
        'endpoints' => [
            'api' => url('/api/ndeye-ndiaye'),
            'docs' => url('/ndiaye/documentation')
        ]
    ]);
});

// Swagger documentation route
Route::get('/ndiaye/documentation', function () {
    $documentation = 'default';
    $urlToDocs = 'http://localhost:8082/docs'; // Force HTTP URL
    $configUrl = config('l5-swagger.defaults.additional_config_url');
    $validatorUrl = config('l5-swagger.defaults.validator_url');
    $operationsSorter = config('l5-swagger.defaults.ui.operations_sort');
    $useAbsolutePath = config('l5-swagger.documentations.'.$documentation.'.paths.use_absolute_path');

    return view('l5-swagger::index', compact(
        'documentation',
        'urlToDocs',
        'configUrl',
        'validatorUrl',
        'operationsSorter',
        'useAbsolutePath'
    ));
})->middleware('cors');
