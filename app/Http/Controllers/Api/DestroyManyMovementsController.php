<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyMovementsRequest;
use App\Services\MovementService;
use Illuminate\Http\JsonResponse;

class DestroyManyMovementsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(DestroyMovementsRequest $request, MovementService $movementService): JsonResponse
    {
        return response()->json([
            'deleted_count' => $movementService->deleteMany($request->validated('movements')),
        ]);
    }
}
