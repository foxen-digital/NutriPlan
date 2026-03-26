<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\IngredientNormalizationAgent;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class IngredientNormalizationService
{
    public function __construct(
        private readonly IngredientParser $ingredientParser
    ) {
    }

    public function normalize(array $ingredientStrings): array
    {
        if ($ingredientStrings === []) {
            return [];
        }

        try {
            $data = $this->normalizeWithLlm($ingredientStrings);
            Log::info('Normalized ingredients', ['data' => $data]);
            return $data;
        } catch (\Throwable $e) {
            Log::error('Failed to normalize ingredients with agent', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->normalizeWithFallback($ingredientStrings);
        }
    }

    private function normalizeWithLlm(array $ingredientStrings): array
    {
        $ingredientsJson = json_encode($ingredientStrings);
        $response = (new IngredientNormalizationAgent())->prompt(
            "Parse the following ingredient strings:\n{$ingredientsJson}",
            provider: config('ai.recipes.normalization.provider'),
        );

        if (! isset($response['ingredients']) || ! is_array($response['ingredients'])) {
            throw new InvalidArgumentException('Invalid structured response from agent');
        }

        $normalizedIngredients = $response['ingredients'];

        // Ensure we have the same number of normalized ingredients as input strings
        if (count($normalizedIngredients) !== count($ingredientStrings)) {
            Log::warning('Agent returned different number of ingredients than provided', [
                'expected' => count($ingredientStrings),
                'received' => count($normalizedIngredients),
            ]);

            // If agent returned too few ingredients, use fallback for the missing ones
            if (count($normalizedIngredients) < count($ingredientStrings)) {
                $missingStrings = array_slice($ingredientStrings, count($normalizedIngredients));
                $missingNormalized = $this->normalizeWithFallback($missingStrings);
                $normalizedIngredients = array_merge($normalizedIngredients, $missingNormalized);
            } else {
                // If agent returned too many, truncate to the expected number
                $normalizedIngredients = array_slice($normalizedIngredients, 0, count($ingredientStrings));
            }
        }

        return $this->validateNormalizedIngredients($normalizedIngredients, $ingredientStrings);
    }

    private function normalizeWithFallback(array $ingredientStrings): array
    {
        Log::warning('Using fallback ingredient parser', ['count' => count($ingredientStrings)]);

        return array_map(function (string $string): array {
            try {
                $parsed = $this->ingredientParser->parse($string);

                return [
                    'base_name' => $parsed['ingredient']->name,
                    'quantity' => $parsed['amount'],
                    'unit' => (string) $parsed['unit']->value,
                    'preparation_notes' => null, // Fallback parser doesn't extract preparation notes
                    'description' => $parsed['ingredient']->name,
                    'original_string' => $string,
                ];
            } catch (\Throwable $e) {
                Log::error('Fallback parser failed for ingredient', [
                    'string' => $string,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'base_name' => $string,
                    'quantity' => null,
                    'unit' => null,
                    'preparation_notes' => null,
                    'description' => $string,
                    'original_string' => $string,
                ];
            }
        }, $ingredientStrings);
    }

    private function validateNormalizedIngredients(array $normalizedIngredients, array $originalStrings): array
    {
        return array_map(function (array $ingredient, string $originalString): array {
            // Ensure all required keys exist
            $validated = [
                'base_name' => $ingredient['base_name'] ?? $originalString,
                'quantity' => $ingredient['quantity'] ?? null,
                'unit' => $ingredient['unit'] ?? null,
                'preparation_notes' => $ingredient['preparation_notes'] ?? null,
                'description' => $ingredient['description'] ?? $ingredient['base_name'] ?? $originalString,
                'original_string' => $originalString,
            ];

            // Ensure quantity is numeric or null
            if (isset($validated['quantity']) && ! is_numeric($validated['quantity'])) {
                $validated['quantity'] = null;
            }

            return $validated;
        }, $normalizedIngredients, $originalStrings);
    }
}
