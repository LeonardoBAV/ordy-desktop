<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrintRequest;
use App\Jobs\PrintJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PrintController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StorePrintRequest $request): JsonResponse
    {
        $path = $request->file('file')->storeAs(
            'prints/'.now()->format('Y/m'),
            Str::uuid().'.pdf',
        );

        PrintJob::dispatch($path);

        return response()->json([
            'path' => $path,
        ], Response::HTTP_ACCEPTED);
    }
}
