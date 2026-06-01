<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovementRequest;
use App\Http\Resources\MovementResource;
use App\Services\MovementService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MovementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreMovementRequest $request, MovementService $movementService): JsonResponse
    {
        $movement = $movementService->create($request->validated());

        return MovementResource::make($movement)
            ->response()
            ->setStatusCode($movement->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }
}
