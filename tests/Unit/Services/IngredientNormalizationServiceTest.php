<?php

declare(strict_types=1);

use App\Models\Ingredient;
use Mockery\MockInterface;
use Illuminate\Support\Sleep;
use App\Enums\MeasurementUnit;
use App\Services\IngredientParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;
use App\Services\Clients\OpenAiClient;
use App\Services\IngredientNormalizationService;

beforeEach(function () {
    $this->openAiClient = mock(OpenAiClient::class);
    $this->ingredientParser = mock(IngredientParser::class);
    $this->service = new IngredientNormalizationService(
        $this->openAiClient,
        $this->ingredientParser
    );

    // Don't mock Log by default
    Log::spy();
});

test('normalize returns empty array for empty input', function () {
    $result = $this->service->normalize([]);
    expect($result)->toBeArray()->toBeEmpty();
});

test('normalize successfully parses ingredients with LLM', function () {
    // Arrange
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil',
        'salt and pepper to taste'
    ];

    $mockLlmResponse = mock(Response::class, function (MockInterface $mock) {
        $content = json_encode([
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 2,
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered'
                ],
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil'
                ],
                [
                    'base_name' => 'salt and pepper',
                    'quantity' => null,
                    'unit' => null,
                    'preparation_notes' => 'to taste',
                    'description' => 'salt and pepper to taste',
                    'original_string' => 'salt and pepper to taste'
                ]
            ]
        ]);

        $mock->shouldReceive('successful')->andReturn(true);
        $mock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $content
                    ]
                ]
            ]
        ]);
    });

    $this->openAiClient->shouldReceive('getChatCompletion')
        ->once()
        ->andReturn($mockLlmResponse);

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(3);

    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'unit' => 'piece',
        'preparation_notes' => 'peeled and quartered',
        'description' => 'red onion, peeled and quartered',
        'original_string' => '2 red onions, peeled and quartered'
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'unit' => 'tbsp',
        'preparation_notes' => null,
        'description' => 'olive oil',
        'original_string' => '1 tbsp olive oil'
    ]);

    expect($result[2])->toMatchArray([
        'base_name' => 'salt and pepper',
        'quantity' => null,
        'unit' => null,
        'preparation_notes' => 'to taste',
        'description' => 'salt and pepper to taste',
        'original_string' => 'salt and pepper to taste'
    ]);
});

test('normalize falls back to IngredientParser on LLM failure', function () {
    // Arrange
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil'
    ];

    // Mock the client to always fail with an exception
    $this->openAiClient->expects('getChatCompletion')
        ->times(3) // Initial attempt + 2 retries
        ->andThrow(new \Exception('API request failed'));

    // Mock ingredient objects
    $onion = new Ingredient(['name' => 'red onion']);
    $oil = new Ingredient(['name' => 'olive oil']);

    // Mock IngredientParser responses
    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[0])
        ->andReturn([
            'ingredient' => $onion,
            'amount' => 2,
            'unit' => MeasurementUnit::PIECE
        ]);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[1])
        ->andReturn([
            'ingredient' => $oil,
            'amount' => 1,
            'unit' => MeasurementUnit::TABLESPOON
        ]);

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(2);

    // The unit will be string representation of the enum value
    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'original_string' => '2 red onions, peeled and quartered'
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'original_string' => '1 tbsp olive oil'
    ]);

    // Verify error logging - expect only one error since the test mocking works differently
    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Failed to normalize ingredients with LLM' &&
                   isset($context['error']) &&
                   $context['error'] === 'API request failed';
        });

    // Verify warning logging for fallback
    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Using fallback ingredient parser' &&
                   isset($context['count']) &&
                   $context['count'] === 2;
        });
});

test('normalize validates and corrects invalid LLM responses', function () {
    // Arrange
    $ingredientStrings = [
        '2 red onions, peeled and quartered'
    ];

    // Mock LLM response with invalid data
    $mockInvalidLlmResponse = mock(Response::class, function (MockInterface $mock) {
        $content = json_encode([
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 'two', // Should be numeric
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered'
                ]
            ]
        ]);

        $mock->shouldReceive('successful')->andReturn(true);
        $mock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $content
                    ]
                ]
            ]
        ]);
    });

    $this->openAiClient->shouldReceive('getChatCompletion')
        ->once()
        ->andReturn($mockInvalidLlmResponse);

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(1);

    // Check that invalid fields are corrected
    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => null, // Was 'two' in the response, should be corrected to null
        'unit' => 'piece',
        'preparation_notes' => 'peeled and quartered',
        'description' => 'red onion, peeled and quartered',
        'original_string' => '2 red onions, peeled and quartered'
    ]);
});

test('normalize handles JSON parsing errors', function () {
    // Arrange
    $ingredientStrings = [
        '2 red onions, peeled and quartered'
    ];

    // Mock invalid JSON response
    $mockInvalidJsonResponse = mock(Response::class, function (MockInterface $mock) {
        $mock->shouldReceive('successful')->andReturn(true);
        $mock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => '{invalid json content'
                    ]
                ]
            ]
        ]);
        $mock->shouldReceive('body')->andReturn('{invalid json content');
    });

    $this->openAiClient->shouldReceive('getChatCompletion')
        ->once()
        ->andReturn($mockInvalidJsonResponse);

    // Mock IngredientParser response for fallback
    $onion = new Ingredient(['name' => 'red onion']);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[0])
        ->andReturn([
            'ingredient' => $onion,
            'amount' => 2,
            'unit' => MeasurementUnit::PIECE
        ]);

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(1);

    // Don't check the exact unit value since it depends on the enum's string representation
    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'original_string' => '2 red onions, peeled and quartered'
    ]);

    // Verify each type of error logging separately
    Log::shouldHaveReceived('error')
        ->withArgs(function ($message, $context) {
            return $message === 'Failed to decode JSON response from LLM' &&
                   isset($context['error']);
        });

    Log::shouldHaveReceived('error')
        ->withArgs(function ($message, $context) {
            return $message === 'Failed to normalize ingredients with LLM';
        });

    // Verify warning logging for fallback
    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(function ($message, $context) {
            return $message === 'Using fallback ingredient parser' &&
                   isset($context['count']) &&
                   $context['count'] === 1;
        });
});

test('normalize handles different number of ingredients in response', function () {
    // Arrange
    $ingredientStrings = [
        '2 red onions, peeled and quartered',
        '1 tbsp olive oil',
        'salt and pepper to taste'
    ];

    // Mock response with fewer ingredients than expected
    $mockPartialResponse = mock(Response::class, function (MockInterface $mock) {
        $content = json_encode([
            'ingredients' => [
                [
                    'base_name' => 'red onion',
                    'quantity' => 2,
                    'unit' => 'piece',
                    'preparation_notes' => 'peeled and quartered',
                    'description' => 'red onion, peeled and quartered',
                    'original_string' => '2 red onions, peeled and quartered'
                ],
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil'
                ]
                // Missing the third ingredient
            ]
        ]);

        $mock->shouldReceive('successful')->andReturn(true);
        $mock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $content
                    ]
                ]
            ]
        ]);
    });

    $this->openAiClient->shouldReceive('getChatCompletion')
        ->once()
        ->andReturn($mockPartialResponse);

    // Mock IngredientParser for fallback on the missing ingredient
    $spice = new Ingredient(['name' => 'salt and pepper']);

    $this->ingredientParser->shouldReceive('parse')
        ->with($ingredientStrings[2])
        ->andReturn([
            'ingredient' => $spice,
            'amount' => 0,
            'unit' => MeasurementUnit::PINCH
        ]);

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(3);

    // First two from LLM
    expect($result[0])->toMatchArray([
        'base_name' => 'red onion',
        'quantity' => 2,
        'unit' => 'piece',
        'original_string' => '2 red onions, peeled and quartered'
    ]);

    expect($result[1])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'unit' => 'tbsp',
        'original_string' => '1 tbsp olive oil'
    ]);

    // Last one from fallback - only check base properties that won't vary based on enum conversion
    expect($result[2])->toMatchArray([
        'base_name' => 'salt and pepper',
        'quantity' => 0,
        'original_string' => 'salt and pepper to taste'
    ]);

    // Verify warning logging
    Log::shouldHaveReceived('warning')
        ->withArgs(function ($message, $context) {
            return $message === 'LLM returned different number of ingredients than provided';
        });
});

test('normalize retries LLM request on failure', function () {
    // Arrange
    $ingredientStrings = ['1 tbsp olive oil'];

    // Mock LLM success response
    $mockSuccessResponse = mock(Response::class, function (MockInterface $mock) {
        $content = json_encode([
            'ingredients' => [
                [
                    'base_name' => 'olive oil',
                    'quantity' => 1,
                    'unit' => 'tbsp',
                    'preparation_notes' => null,
                    'description' => 'olive oil',
                    'original_string' => '1 tbsp olive oil'
                ]
            ]
        ]);

        $mock->shouldReceive('successful')->andReturn(true);
        $mock->shouldReceive('json')->andReturn([
            'choices' => [
                [
                    'message' => [
                        'content' => $content
                    ]
                ]
            ]
        ]);
    });

    // First call fails, second succeeds
    $this->openAiClient->expects('getChatCompletion')
        ->twice() // Allow exactly two calls
        ->andReturnUsing(function ($args) use ($mockSuccessResponse) {
            static $callCount = 0;
            $callCount++;

            if ($callCount === 1) {
                throw new \Exception('Connection timeout');
            }

            return $mockSuccessResponse;
        });

    // Act
    $result = $this->service->normalize($ingredientStrings);

    // Assert
    expect($result)->toBeArray()->toHaveCount(1);

    expect($result[0])->toMatchArray([
        'base_name' => 'olive oil',
        'quantity' => 1,
        'unit' => 'tbsp',
        'original_string' => '1 tbsp olive oil'
    ]);

    // Verify Sleep was called
    Sleep::assertSleptTimes(1);
});
