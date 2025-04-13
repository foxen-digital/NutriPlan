<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeasurementUnit;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Ingredient>
     */
    protected $model = Ingredient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'is_common' => false,
        ];
    }

    /**
     * Indicate that the ingredient is commonly found in kitchens.
     */
    public function common(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_common' => true,
        ]);
    }

    /**
     * Create an ingredient with its relationship to a recipe.
     */
    public function forRecipe(): static
    {
        return $this->state(fn (array $attributes): array => [])
            ->afterCreating(function (Ingredient $ingredient): void {
                // Occasionally use null amount for ingredients like 'to taste' items
                $useNullAmount = fake()->boolean(20); // 20% chance of null amount

                $ingredient->recipes()->attach(
                    Recipe::factory()->create(),
                    [
                        'amount' => $useNullAmount ? null : fake()->randomFloat(2, 0.25, 10),
                        'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                        'description' => fake()->optional()->sentence(),
                    ]
                );
            });
    }
}
