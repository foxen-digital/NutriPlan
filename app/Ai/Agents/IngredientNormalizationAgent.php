<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
class IngredientNormalizationAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'EOT'
You are an expert recipe ingredient parser. You will be given a list of ingredient strings. Your task is to return a single JSON object containing a key 'ingredients', whose value is an array. Each object in the array should represent one ingredient from the input list and have the following keys:
- 'base_name': The common name of the ingredient (e.g., "red onion", "olive oil", "salt"). Normalize the name.
- 'quantity': The numeric quantity (e.g., 2, 1). Use 0 or null if not applicable (like "to taste").
- 'unit': The unit of measurement (e.g., "piece", "tbsp", "pinch"). Use standard abbreviations or full names. Use null or "unit" if not applicable.
- 'preparation_notes': Any preparation instructions or extra details present in the original string (e.g., "peeled and quartered", "to taste"). Use null if none.
- 'description': Combine the 'base_name' and 'preparation_notes' into a descriptive string.
- 'original_string': The full, unmodified original ingredient string.

Follow the structure precisely. Ensure quantity is a number or null. Ensure unit is a string or null. Ensure preparation_notes is a string or null.
EOT;
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'ingredients' => $schema->array()->items(
                $schema->object([
                    'base_name' => $schema->string()->required(),
                    'quantity' => $schema->number()->nullable()->required(),
                    'unit' => $schema->string()->nullable()->required(),
                    'preparation_notes' => $schema->string()->nullable()->required(),
                    'description' => $schema->string()->required(),
                    'original_string' => $schema->string()->required(),
                ])->withoutAdditionalProperties()
            )->required(),
        ];
    }
}
