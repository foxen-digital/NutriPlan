<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Clients\OpenAiClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use InvalidArgumentException;

class InstructionNormalizationService
{
    private const SYSTEM_PROMPT = <<<'EOT'
You are a helpful assistant that converts cooking and recipe instructions into clean, structured Markdown format.

Your task is to improve readability and formatting without changing the content or order of steps.

Formatting guidelines:
- Use numbered lists for sequential instructions.
- Use bold (`**...**`) to highlight important ingredients and timings (e.g., **onions**, **10 minutes**).
- Use italics (`*...*`) for optional notes or helpful tips (e.g., *if desired*, *note: do not overmix*).
- Only include headings (e.g., `## Slow Cooker Directions`) if they are present in the input or clearly indicated by context (such as multiple preparation methods). Do not add generic headings like `## Instructions`.
- Do not add or remove any steps, ingredients, or instructions unless explicitly told to.
- Do not include ingredient lists unless they are part of the input.
- Return only the cleaned-up Markdown-formatted version of the instructions, with no extra explanation.

Your goal is to make the instructions easier to read and use, while preserving their original meaning and tone.
EOT;

    public function __construct(
        private readonly OpenAiClient $openAiClient
    ) {
    }

    public function normalize(string $rawInstructions): string
    {
        if (empty($rawInstructions)) {
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
        $prompt = $this->buildPrompt($rawInstructions);
        $response = $this->makeLlmRequest($prompt);

        $data = $response->json();

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new InvalidArgumentException('Unexpected response format from LLM API');
        }

        $content = $data['choices'][0]['message']['content'];

        if (empty($content)) {
            throw new InvalidArgumentException('Empty response from LLM');
        }

        return $content;
    }

    private function buildPrompt(string $rawInstructions): array
    {
        return [
            [
                'role' => 'system',
                'content' => self::SYSTEM_PROMPT,
            ],
            [
                'role' => 'user',
                'content' => "Format the following recipe instructions into clean Markdown:\n\n{$rawInstructions}"
            ]
        ];
    }

    private function makeLlmRequest(array $prompt, int $attempt = 1): Response
    {
        $maxAttempts = 3;

        try {
            $response = $this->openAiClient->getChatCompletion($prompt, [
                'temperature' => 0.2,
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

    private function normalizeWithFallback(string $rawInstructions): string
    {
        Log::warning('Using fallback for instruction normalization');
        return $rawInstructions;
    }
}
