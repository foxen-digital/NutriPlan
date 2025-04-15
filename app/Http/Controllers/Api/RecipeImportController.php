<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ImportRecipeRequest;
use App\Jobs\ImportRecipeJob;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RecipeImportController extends Controller
{
    /**
     * Queue a recipe import from a URL via the API.
     *
     * This endpoint is designed for browser extensions and other API clients
     * to submit URLs for recipe importing.
     *
     * @param ImportRecipeRequest $request The validated request containing the recipe URL
     * @return JsonResponse Response indicating the recipe import has been queued
     */
    public function __invoke(ImportRecipeRequest $request): JsonResponse
    {
        // Dispatch the job to import the recipe asynchronously
        ImportRecipeJob::dispatch(
            $request->input('url'),
            $request->user()->id
        );

        // Return an immediate response to the API client
        return response()->json(
            ['message' => 'Recipe import queued successfully.'],
            Response::HTTP_ACCEPTED
        );
    }
}
