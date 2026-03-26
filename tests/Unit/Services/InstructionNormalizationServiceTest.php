<?php

declare(strict_types=1);

use App\Ai\Agents\InstructionFormattingAgent;
use App\Services\InstructionNormalizationService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->service = new InstructionNormalizationService();

    Log::spy();
});

test('normalize returns empty string when input is empty', function () {
    $result = $this->service->normalize('');

    expect($result)->toBe('');
});

test('normalize returns formatted instructions when agent call succeeds', function () {
    $rawInstructions = "Preheat oven to the right temperature.\nMix the dry ingredients.\nAdd wet ingredients and stir well.";
    $expectedOutput = "1. Preheat oven to the right **temperature**.\n2. Mix the **dry ingredients**.\n3. Add **wet ingredients** and stir well.";

    InstructionFormattingAgent::fake([$expectedOutput]);

    Log::shouldReceive('info')
        ->once()
        ->with('Normalized instructions', ['length' => strlen($expectedOutput)]);

    $result = $this->service->normalize($rawInstructions);

    expect($result)->toBe($expectedOutput);
});

test('normalize uses fallback when agent call fails', function () {
    $rawInstructions = "Cook until golden brown.";

    InstructionFormattingAgent::fake(function () {
        throw new \Exception('API call failed');
    });

    Log::shouldReceive('error')->once();
    Log::shouldReceive('warning')
        ->once()
        ->with('Using fallback for instruction normalization');

    $result = $this->service->normalize($rawInstructions);

    expect($result)->toBe($rawInstructions);
});
