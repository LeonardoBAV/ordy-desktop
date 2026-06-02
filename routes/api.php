<?php

use App\Http\Controllers\Api\MovementController;
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

Route::post('movements', MovementController::class)
    ->name('movements.store');
