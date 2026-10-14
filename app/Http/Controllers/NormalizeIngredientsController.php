<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Ingredient\NormalizeIngredientsRequest;
use App\Models\Ingredient;
use App\Services\IngredientNormalizationService;
use Illuminate\Http\JsonResponse;

class NormalizeIngredientsController extends Controller
{
    public function __invoke(
        NormalizeIngredientsRequest $request,
        IngredientNormalizationService $normalizationService
    ): JsonResponse {
        $ingredientStrings = collect($request->validated('ingredients'))
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => $line !== '')
            ->values()
            ->all();

        $normalizedIngredients = $normalizationService->normalize($ingredientStrings);

        $ingredients = [];
        foreach ($normalizedIngredients as $normalizedData) {
            $baseName = trim((string) ($normalizedData['base_name'] ?? ''));

            if ($baseName === '') {
                continue;
            }

            $ingredient = Ingredient::firstOrCreate(['name' => $baseName]);

            $ingredients[] = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'amount' => $normalizedData['quantity'] ?? null,
                'unit' => $normalizedData['unit'] ?? null,
                'description' => $normalizedData['description'] ?? null,
                'original_string' => $normalizedData['original_string'] ?? $baseName,
            ];
        }

        return response()->json(['ingredients' => $ingredients]);
    }
}
