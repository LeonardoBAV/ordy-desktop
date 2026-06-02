<?php

use App\Http\Controllers\Api\MovementController;
use App\Services\LocalNetworkService;
use Illuminate\Support\Facades\Route;

Route::get('healthy', fn () => response()->json(['status' => 'ok']))
    ->name('healthy');

Route::get('local-network', function (LocalNetworkService $localNetworkService) {
    $baseUrl = $localNetworkService->baseUrl();

    return response()->json([
        'ip' => $localNetworkService->localIp(),
        'port' => $localNetworkService->port(),
        'url' => $baseUrl,
        'healthy_url' => $baseUrl ? "{$baseUrl}/api/healthy" : null,
    ]);
})->name('local-network');

Route::post('movements', MovementController::class)
    ->name('movements.store');
