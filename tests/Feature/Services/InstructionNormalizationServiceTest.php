<?php

declare(strict_types=1);

use App\Services\InstructionNormalizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('service can be resolved from container', function () {
    $service = $this->app->make(InstructionNormalizationService::class);

    expect($service)->toBeInstanceOf(InstructionNormalizationService::class);
});

test('service returns normalized instructions', function () {
    // Mock the HTTP client to return a specific response
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => "1. **Preheat** oven to **350°F**.\n2. Mix dry ingredients in a bowl.\n3. *For best results, sift flour before mixing.*"
                    ]
                ]
            ]
        ], 200)
    ]);

    $service = $this->app->make(InstructionNormalizationService::class);

    $rawInstructions = "Preheat oven to 350°F. Mix dry ingredients in a bowl. For best results, sift flour before mixing.";
    $result = $service->normalize($rawInstructions);

    expect($result)
        ->toContain('**Preheat**')
        ->toContain('**350°F**')
        ->toContain('*For best results');
});

test('service returns original instructions when api fails', function () {
    // Mock the HTTP client to fail
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([], 500)
    ]);

    $service = $this->app->make(InstructionNormalizationService::class);

    $rawInstructions = "Preheat oven to 350°F. Mix dry ingredients in a bowl.";
    $result = $service->normalize($rawInstructions);

    expect($result)->toBe($rawInstructions);
});
