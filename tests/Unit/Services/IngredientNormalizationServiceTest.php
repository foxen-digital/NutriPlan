<?php

declare(strict_types=1);

use App\Ai\Agents\IngredientNormalizationAgent;
use App\Models\Ingredient;
use App\Enums\MeasurementUnit;
use App\Services\IngredientParser;
use App\Services\IngredientNormalizationService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->ingredientParser = mock(IngredientParser::class);
    $this->service = new IngredientNormalizationService($this->ingredientParser);

    Log::spy();
});

test('normalize returns empty array for empty input', function () {
    $result = $this->service->normalize([]);
    expect($result)->toBeArray()->toBeEmpty();
});

test('normalize successfully parses ingredients with agent', function () {
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil',
        'salt and pepper to taste',
    ];

    IngredientNormalizationAgent::fake([
        [
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 2,
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered',
                ],
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil',
                ],
                [
                    'base_name' => 'salt and pepper',
                    'quantity' => null,
                    'unit' => null,
                    'preparation_notes' => 'to taste',
                    'description' => 'salt and pepper to taste',
                    'original_string' => 'salt and pepper to taste',
                ],
            ],
        ],
    ]);

    $result = $this->service->normalize($ingredientStrings);

    expect($result)->toBeArray()->toHaveCount(3);

    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'unit' => 'piece',
        'preparation_notes' => 'peeled and quartered',
        'description' => 'red onion, peeled and quartered',
        'original_string' => '2 red onions, peeled and quartered',
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'unit' => 'tbsp',
        'preparation_notes' => null,
        'description' => 'olive oil',
        'original_string' => '1 tbsp olive oil',
    ]);

    expect($result[2])->toMatchArray([
        'base_name' => 'salt and pepper',
        'quantity' => null,
        'unit' => null,
        'preparation_notes' => 'to taste',
        'description' => 'salt and pepper to taste',
        'original_string' => 'salt and pepper to taste',
    ]);
});

test('normalize falls back to IngredientParser on agent failure', function () {
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil',
    ];

    IngredientNormalizationAgent::fake(function () {
        throw new \Exception('API request failed');
    });

    $onion = new Ingredient(['name' => 'red onion']);
    $oil = new Ingredient(['name' => 'olive oil']);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[0])
        ->andReturn([
            'ingredient' => $onion,
            'amount' => 2,
            'unit' => MeasurementUnit::PIECE,
        ]);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[1])
        ->andReturn([
            'ingredient' => $oil,
            'amount' => 1,
            'unit' => MeasurementUnit::TABLESPOON,
        ]);

    $result = $this->service->normalize($ingredientStrings);

    expect($result)->toBeArray()->toHaveCount(2);

    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'original_string' => '2 red onions, peeled and quartered',
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'original_string' => '1 tbsp olive oil',
    ]);

    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Failed to normalize ingredients with LLM' &&
                   isset($context['error']) &&
                   $context['error'] === 'API request failed';
        });

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Using fallback ingredient parser' &&
                   isset($context['count']) &&
                   $context['count'] === 2;
        });
});

test('normalize validates and corrects non-numeric quantity in response', function () {
    $ingredientStrings = ['2 red onions, peeled and quartered'];

    IngredientNormalizationAgent::fake([
        [
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 'two', // Should be numeric
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered',
                ],
            ],
        ],
    ]);

    $result = $this->service->normalize($ingredientStrings);

    expect($result)->toBeArray()->toHaveCount(1);
    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => null, // Was 'two', corrected to null
        'unit' => 'piece',
        'preparation_notes' => 'peeled and quartered',
        'original_string' => '2 red onions, peeled and quartered',
    ]);
});

test('normalize handles different number of ingredients in response', function () {
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil',
        'salt and pepper to taste',
    ];

    // Agent returns only 2 of the 3 ingredients
    IngredientNormalizationAgent::fake([
        [
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 2,
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered',
                ],
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil',
                ],
            ],
        ],
    ]);

    $spice = new Ingredient(['name' => 'salt and pepper']);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[2])
        ->andReturn([
            'ingredient' => $spice,
            'amount' => 0,
            'unit' => MeasurementUnit::PINCH,
        ]);

    $result = $this->service->normalize($ingredientStrings);

    expect($result)->toBeArray()->toHaveCount(3);

    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'unit' => 'piece',
        'original_string' => '2 red onions, peeled and quartered',
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'unit' => 'tbsp',
        'original_string' => '1 tbsp olive oil',
    ]);

    expect($result[2])->toMatchArray([
        'base_name' => 'salt and pepper',
        'quantity' => 0,
        'original_string' => 'salt and pepper to taste',
    ]);

    Log::shouldHaveReceived('warning')
        ->withArgs(function ($message, $context) {
            return $message === 'Agent returned different number of ingredients than provided';
        });
});
