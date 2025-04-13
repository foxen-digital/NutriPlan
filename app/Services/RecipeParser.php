<?php

namespace App\Services;

use Exception;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Support\Str;
use Brick\StructuredData\Item;
use Illuminate\Support\Facades\Auth;

class RecipeParser
{
    private ?array $nutrition = null;
    private ?Recipe $recipe = null;

    public function __construct(
        private string $title = '',
        private string $description = '',
        private readonly string $url = '',
        private string $author = '',
        /** @var array<int, string> */
        private array $ingredients = [],
        /** @var array<int, string> */
        private array $steps = [],
        private string $yield = '',
        private int $prep_time = 0,
        private int $cooking_time = 0,
        private int $servings = 0,
        /** @var array<int, string> */
        private array $images = [],
        /** @var array<int, string> */
        private array $categories = [],
        private ?IngredientNormalizationService $normalizationService = null,
        private ?InstructionNormalizationService $instructionNormalizationService = null,
        private readonly NutritionParser $nutrition_parser = new NutritionParser()
    ) {
        if (!$this->normalizationService instanceof \App\Services\IngredientNormalizationService) {
            $this->normalizationService = app(IngredientNormalizationService::class);
        }

        if (!$this->instructionNormalizationService instanceof \App\Services\InstructionNormalizationService) {
            $this->instructionNormalizationService = app(InstructionNormalizationService::class);
        }
    }

    /**
     * @param array<int, Item>|Item $items
     */
    public static function fromItems(array|Item $items, string $url): ?Recipe
    {
        foreach ($items as $item) {
            if (Str::contains(Str::lower(implode(',', $item->getTypes())), 'recipe')) {
                return (new self(url: $url))->parse($item);
            }
        }

        // The whole thing might be a recipe
        if (count($items) == 1) {
            return (new self(url: $url))->parse(is_array($items) ? $items[0] : $items);
        }

        return null;
    }


    /**
     * Set an existing recipe to update
     */
    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function parse(Item $item): Recipe
    {
        foreach ($item->getProperties() as $name => $values) {
            $fn = "parse_" . Str::replace(['http://schema.org/', 'https://schema.org/'], '', Str::lower($name));

            if (method_exists($this, $fn)) {
                $this->$fn($values);
            }

            // Special handling for nutrition data which might be under different property names
            if (Str::contains(Str::lower($name), 'nutrition') && ($this->nutrition === null || $this->nutrition === [])) {
                $nutritionData = $this->nutrition_parser->parse($values);
                if ($nutritionData !== null && $nutritionData !== []) {
                    $this->nutrition = $nutritionData;
                }
            }
        }

        // Use the existing recipe if set, otherwise find or create a new one
        $recipe = $this->recipe ?? Recipe::firstOrNew([
            'user_id' => Auth::id(),
            'url' => $this->url,
        ])->fill([
            'title' => $this->title,
            'author' => $this->author,
            'description' => $this->description,
            'instructions' => $this->instructionNormalizationService->normalize(implode("\n", $this->steps)),
            'prep_time' => $this->prep_time,
            'cooking_time' => $this->cooking_time,
            'servings' => $this->servings !== 0 ? $this->servings : (int) $this->yield,
            'images' => array_values(array_filter($this->images)),
        ]);

        // Save the recipe first if it's new
        if (!$recipe->exists) {
            $recipe->save();
        }

        // Handle categories
        $category_names = array_unique(array_filter($this->categories));
        $categories = collect($category_names)->map(fn (string $name): Category => Category::firstOrCreate(
            ['name' => trim($name)],
            ['is_active' => true]
        ));

        $recipe->categories()->sync($categories->pluck('id'));

        // Parse and attach ingredients
        $rawIngredientStrings = array_filter($this->ingredients);
        $normalizedIngredients = $this->normalizationService->normalize($rawIngredientStrings);

        // First detach all existing ingredients
        $recipe->ingredients()->detach();

        // Then attach each ingredient individually to allow duplicates
        foreach ($normalizedIngredients as $normalizedData) {
            // Skip if base_name is missing or empty
            if (!isset($normalizedData['base_name'])) {
                continue;
            }
            if (empty($normalizedData['base_name'])) {
                continue;
            }
            $ingredient = Ingredient::firstOrCreate(['name' => $normalizedData['base_name']]);

            $recipe->ingredients()->attach($ingredient->id, [
                'amount' => $normalizedData['quantity'] ?? null,
                'unit' => $normalizedData['unit'] ?? null,
                'description' => $normalizedData['description'] ?? $normalizedData['original_string'] ?? $normalizedData['base_name'],
            ]);
        }

        // Handle nutrition information if available
        if ($this->nutrition !== null && $this->nutrition !== []) {
            if ($recipe->nutritionInformation) {
                // Update existing nutrition information
                $recipe->nutritionInformation->update($this->nutrition);
            } else {
                // Create new nutrition information
                $recipe->nutritionInformation()->create($this->nutrition);
            }

            // Refresh the recipe to ensure the nutrition information is loaded
            $recipe->refresh();
        }

        return $recipe;
    }

    /**
     * @param array<int, string>|string $values
     */
    private function parse_name(array|string $values): void
    {
        $this->title = (is_array($values) ? $values[0] : $values);
    }

    /**
     * @param array<int, string>|string $values
     */
    private function parse_description(array|string $values): void
    {
        $this->description = (is_array($values) ? $values[0] : $values);
    }

    /**
     * @param array<int, string>|string $values
     */
    public function parse_recipeyield(array|string $values): void
    {
        $value = is_array($values) ? $values[0] : $values;

        // Try to extract a number from the yield string
        if (preg_match('/\d+/', $value, $matches)) {
            $this->servings = (int) $matches[0];
        }

        $this->yield = $value;
    }

    /**
     * @param array<int, string>|string $values
     */
    public function parse_preptime(array|string $values): void
    {
        $time = is_array($values) ? $values[0] : $values;

        if (preg_match('/PT(\d+H)?(\d+M)?/', $time, $matches)) {
            $hours = isset($matches[1]) ? (int) rtrim($matches[1], 'H') : 0;
            $minutes = isset($matches[2]) ? (int) rtrim($matches[2], 'M') : 0;

            $this->prep_time = ($hours * 60) + $minutes;
        }
    }

    /**
     * @param array<int, string>|string $values
     */
    public function parse_cooktime(array|string $values): void
    {
        $time = is_array($values) ? $values[0] : $values;

        if (preg_match('/PT(\d+H)?(\d+M)?/', $time, $matches)) {
            $hours = isset($matches[1]) ? (int) rtrim($matches[1], 'H') : 0;
            $minutes = isset($matches[2]) ? (int) rtrim($matches[2], 'M') : 0;

            $this->cooking_time = ($hours * 60) + $minutes;
        }
    }

    /**
     * @param array<int, Item|string> $values
     */
    public function parse_image(array $values): void
    {
        foreach ($values as $item) {
            if ($item instanceof Item) {
                foreach ($item->getProperties() as $name => $values) {
                    $name = Str::replace(['http://schema.org/', 'https://schema.org/'], '', Str::lower($name));
                    // $name may be one of [url, height, thumbnail, width]
                    // If it's relative
                    if ($name == "url" && Str::contains($values[0], ["http://", "https://"])) {
                        $this->images[] = $values[0];
                    }
                }
            } elseif (is_array($item)) {
                throw new Exception("Handle image items are array of strings");
            } elseif (Str::contains($item, ["http://", "https://"])) {
                $this->images[] = $item;
            }
        }
    }

    /**
     * @param array<int, string>|string $values
     */
    public function parse_recipeingredient(array|string $values): void
    {
        if (is_array($values)) {
            $this->ingredients = array_merge(
                collect($values)->transform(fn (string $item): string => html_entity_decode($item))->toArray()
            );
        }
    }

    /**
     * @param array<int, Item|string> $values
     */
    public function parse_recipeinstructions(array $values): void
    {
        foreach ($values as $item) {
            if ($item instanceof Item) {
                if (Str::contains(Str::lower(implode(',', $item->getTypes())), 'howtostep')) {
                    foreach ($item->getProperties() as $name => $values) {
                        $name = Str::replace(['http://schema.org/', 'https://schema.org/'], '', Str::lower($name));
                        if ($name == "text") {
                            $this->steps[] = html_entity_decode($values[0]);
                        }
                    }
                }
            } else {
                $this->steps[] = html_entity_decode($item);
            }
        }
    }

    /**
     * @param array<int, Item|string> $values
     */
    public function parse_author(array $values): void
    {
        foreach ($values as $item) {
            if ($item instanceof Item) {
                if (Str::contains(Str::lower(implode(',', $item->getTypes())), 'person')) {
                    foreach ($item->getProperties() as $name => $values) {
                        $name = Str::replace(['http://schema.org/', 'https://schema.org/'], '', Str::lower($name));
                        if ($name == "name") {
                            $this->author = html_entity_decode($values[0]);
                        }
                    }
                }
            } else {
                $this->author = html_entity_decode($item);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function parseCommaSeparatedString(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->toArray();
    }

    /**
     * @param array<int, string|array<int, string>>|string $values
     */
    public function parse_keywords(array|string $values): void
    {
        if (is_array($values)) {
            $keywords = collect($values)->map(function (string|array $item): array|string {
                $value = is_array($item) ? $item[0] : $item;
                return Str::contains($value, ',')
                    ? $this->parseCommaSeparatedString($value)
                    : $value;
            })->flatten()->toArray();

            $this->categories = array_merge($this->categories, $keywords);
        } else {
            $categories = Str::contains($values, ',')
                ? $this->parseCommaSeparatedString($values)
                : [$values];

            $this->categories = array_merge($this->categories, $categories);
        }
    }

    /**
     * @param array<int, string|array<int, string>>|string $values
     */
    public function parse_recipecategory(array|string $values): void
    {
        if (is_array($values)) {
            $categories = collect($values)->map(function (string|array $item): array|string {
                $value = is_array($item) ? $item[0] : $item;
                return Str::contains($value, ',')
                    ? $this->parseCommaSeparatedString($value)
                    : $value;
            })->flatten()->toArray();

            $this->categories = array_merge($this->categories, $categories);
        } else {
            $categories = Str::contains($values, ',')
                ? $this->parseCommaSeparatedString($values)
                : [$values];

            $this->categories = array_merge($this->categories, $categories);
        }
    }


}
