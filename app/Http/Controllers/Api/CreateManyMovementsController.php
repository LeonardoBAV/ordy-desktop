<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManyMovementsRequest;
use App\Http\Resources\MovementResource;
use App\Models\Movement;
use App\Services\MovementService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CreateManyMovementsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreManyMovementsRequest $request, MovementService $movementService): JsonResponse
    {
        $movements = $movementService->createMany($request->validated('movements'));
        $movements->load('product');

        return MovementResource::collection($movements)
            ->response()
            ->setStatusCode($movements->contains(fn (Movement $movement): bool => $movement->wasRecentlyCreated)
                ? Response::HTTP_CREATED
                : Response::HTTP_OK);
    }
}
