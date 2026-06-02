<?php

use App\Http\Controllers\Api\MovementController;
use App\Services\LocalNetworkService;
use Illuminate\Support\Facades\Route;

Route::get('healthy', function (LocalNetworkService $localNetworkService) {
    $baseUrl = $localNetworkService->baseUrl();

    return response()->json([
        'status' => 'ok',
        'ip' => $localNetworkService->localIp(),
        'port' => $localNetworkService->port(),
        'url' => $baseUrl,
        'healthy_url' => $baseUrl ? "{$baseUrl}/api/healthy" : null,
    ]);
})->name('healthy');

Route::post('movements', MovementController::class)
    ->name('movements.store');
