<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Clients\OpenAiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use Illuminate\Http\Client\RequestException;
use InvalidArgumentException;
use JsonException;

class IngredientNormalizationService
{
    public function __construct(
        private readonly OpenAiClient $openAiClient,
        private readonly IngredientParser $ingredientParser
    ) {
    }

    public function normalize(array $ingredientStrings): array
    {
        if ($ingredientStrings === []) {
            return [];
        }

        try {
            return $this->normalizeWithLlm($ingredientStrings);
        } catch (\Throwable $e) {
            Log::error('Failed to normalize ingredients with LLM', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->normalizeWithFallback($ingredientStrings);
        }
    }

    private function normalizeWithLlm(array $ingredientStrings): array
    {
        $prompt = $this->buildPrompt($ingredientStrings);
        $response = $this->makeLlmRequest($prompt);

        try {
            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                throw new InvalidArgumentException('Unexpected response format from LLM API');
            }

            $contentString = $data['choices'][0]['message']['content'];
            $content = json_decode($contentString, true, 512, JSON_THROW_ON_ERROR);

            if (!isset($content['ingredients']) || !is_array($content['ingredients'])) {
                throw new InvalidArgumentException('Invalid JSON response structure from LLM');
            }

            $normalizedIngredients = $content['ingredients'];

            // Ensure we have the same number of normalized ingredients as input strings
            if (count($normalizedIngredients) !== count($ingredientStrings)) {
                Log::warning('LLM returned different number of ingredients than provided', [
                    'expected' => count($ingredientStrings),
                    'received' => count($normalizedIngredients),
                ]);

                // If LLM returned too few ingredients, use fallback for the missing ones
                if (count($normalizedIngredients) < count($ingredientStrings)) {
                    $missingStrings = array_slice($ingredientStrings, count($normalizedIngredients));
                    $missingNormalized = $this->normalizeWithFallback($missingStrings);
                    $normalizedIngredients = array_merge($normalizedIngredients, $missingNormalized);
                } else {
                    // If LLM returned too many, truncate to the expected number
                    $normalizedIngredients = array_slice($normalizedIngredients, 0, count($ingredientStrings));
                }
            }

            return $this->validateNormalizedIngredients($normalizedIngredients, $ingredientStrings);
        } catch (JsonException $e) {
            Log::error('Failed to decode JSON response from LLM', [
                'error' => $e->getMessage(),
                'response' => $response->body(),
            ]);

            throw $e;
        }
    }

    private function buildPrompt(array $ingredientStrings): array
    {
        $ingredientsJson = json_encode($ingredientStrings);

        return [
            [
                'role' => 'system',
                'content' => "You are an expert recipe ingredient parser. You will be given a list of ingredient strings. Your task is to return a single JSON object containing a key 'ingredients', whose value is an array. Each object in the array should represent one ingredient from the input list and have the following keys:\n- 'base_name': The common name of the ingredient (e.g., \"red onion\", \"olive oil\", \"salt\"). Normalize the name.\n- 'quantity': The numeric quantity (e.g., 2, 1). Use 0 or null if not applicable (like \"to taste\").\n- 'unit': The unit of measurement (e.g., \"piece\", \"tbsp\", \"pinch\"). Use standard abbreviations or full names. Use null or \"unit\" if not applicable.\n- 'preparation_notes': Any preparation instructions or extra details present in the original string (e.g., \"peeled and quartered\", \"to taste\"). Use null if none.\n- 'description': Combine the 'base_name' and 'preparation_notes' into a descriptive string.\n- 'original_string': The full, unmodified original ingredient string.\n\nFollow the structure precisely. Ensure quantity is a number or null. Ensure unit is a string or null. Ensure preparation_notes is a string or null."
            ],
            [
                'role' => 'user',
                'content' => "Parse the following ingredient strings:\n{$ingredientsJson}"
            ]
        ];
    }

    private function makeLlmRequest(array $prompt, int $attempt = 1): \Illuminate\Http\Client\Response
    {
        $maxAttempts = 3;

        try {
            $response = $this->openAiClient->getChatCompletion($prompt, [
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

            if ($response->successful()) {
                return $response;
            }

            if ($attempt < $maxAttempts) {
                $backoff = 2 ** $attempt; // Exponential backoff: 2, 4, 8, etc.
                Sleep::for($backoff)->seconds();
                return $this->makeLlmRequest($prompt, $attempt + 1);
            }

            throw new RequestException($response);
        } catch (\Throwable $e) {
            if ($attempt < $maxAttempts) {
                $backoff = 2 ** $attempt; // Exponential backoff: 2, 4, 8, etc.
                Sleep::for($backoff)->seconds();
                return $this->makeLlmRequest($prompt, $attempt + 1);
            }

            throw $e;
        }
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
            if (isset($validated['quantity']) && $validated['quantity'] !== null && !is_numeric($validated['quantity'])) {
                $validated['quantity'] = null;
            }

            return $validated;
        }, $normalizedIngredients, $originalStrings);
    }
}
