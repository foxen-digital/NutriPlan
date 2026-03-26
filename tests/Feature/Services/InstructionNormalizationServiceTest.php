<?php

declare(strict_types=1);

use App\Ai\Agents\InstructionFormattingAgent;
use App\Services\InstructionNormalizationService;

test('service can be resolved from container', function () {
    $service = $this->app->make(InstructionNormalizationService::class);

    expect($service)->toBeInstanceOf(InstructionNormalizationService::class);
});

test('service returns normalized instructions', function () {
    $expectedOutput = "1. **Preheat** oven to **350°F**.\n2. Mix dry ingredients in a bowl.\n3. *For best results, sift flour before mixing.*";

    InstructionFormattingAgent::fake([$expectedOutput]);

    $service = $this->app->make(InstructionNormalizationService::class);

    $rawInstructions = "Preheat oven to 350°F. Mix dry ingredients in a bowl. For best results, sift flour before mixing.";
    $result = $service->normalize($rawInstructions);

    expect($result)
        ->toContain('**Preheat**')
        ->toContain('**350°F**')
        ->toContain('*For best results');
});

test('service returns original instructions when agent fails', function () {
    InstructionFormattingAgent::fake(function () {
        throw new \Exception('Agent error');
    });

    $service = $this->app->make(InstructionNormalizationService::class);

    $rawInstructions = "Preheat oven to 350°F. Mix dry ingredients in a bowl.";
    $result = $service->normalize($rawInstructions);

    expect($result)->toBe($rawInstructions);
});
