<?php

use App\Http\Controllers\Api\CreateManyMovementsController;
use App\Http\Controllers\Api\CreateMovementController;
use App\Http\Controllers\Api\DestroyManyMovementsController;
use App\Http\Controllers\Api\PrintController;
use App\Services\LocalNetworkService;
use Illuminate\Support\Facades\Route;

$healthyCorsHeaders = [
    'Access-Control-Allow-Headers' => 'Accept, Content-Type',
    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
    'Access-Control-Allow-Origin' => '*',
];

Route::options('healthy', fn () => response()->noContent(204, $healthyCorsHeaders));

Route::get('healthy', function (LocalNetworkService $localNetworkService) use ($healthyCorsHeaders) {
    $baseUrl = $localNetworkService->baseUrl();

    return response()->json([
        'status' => 'ok',
        'ip' => $localNetworkService->localIp(),
        'port' => $localNetworkService->port(),
        'url' => $baseUrl,
        'healthy_url' => $baseUrl ? "{$baseUrl}/api/healthy" : null,
    ], headers: $healthyCorsHeaders);
})->name('healthy');

Route::post('print', PrintController::class)
    ->name('print.store');

Route::prefix('movements')->name('movements.')->group(function (): void {
    Route::post('many', CreateManyMovementsController::class)
        ->name('store-many');

    Route::post('/', CreateMovementController::class)
        ->name('store');

    Route::delete('/', DestroyManyMovementsController::class)
        ->name('destroy');
});
