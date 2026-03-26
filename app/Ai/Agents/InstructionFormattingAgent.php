<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class InstructionFormattingAgent implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'EOT'
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
    }
}
