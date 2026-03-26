<?php

declare(strict_types=1);

namespace App\Services;

use App\Ai\Agents\InstructionFormattingAgent;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class InstructionNormalizationService
{
    public function normalize(string $rawInstructions): string
    {
        if ($rawInstructions === '' || $rawInstructions === '0') {
            return '';
        }

        try {
            $normalized = $this->normalizeWithLlm($rawInstructions);
            Log::info('Normalized instructions', ['length' => strlen($normalized)]);
            return $normalized;
        } catch (\Throwable $e) {
            Log::error('Failed to normalize instructions with LLM', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->normalizeWithFallback($rawInstructions);
        }
    }

    private function normalizeWithLlm(string $rawInstructions): string
    {
        $response = (new InstructionFormattingAgent())->prompt(
            "Format the following recipe instructions into clean Markdown:\n\n{$rawInstructions}",
            provider: config('ai.recipes.normalization.provider'),
        );

        $content = (string) $response;

        if (empty($content)) {
            throw new InvalidArgumentException('Empty response from agent');
        }

        return $content;
    }

    private function normalizeWithFallback(string $rawInstructions): string
    {
        Log::warning('Using fallback for instruction normalization');
        return $rawInstructions;
    }
}
