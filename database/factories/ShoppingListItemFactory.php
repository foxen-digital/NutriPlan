<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShoppingList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShoppingListItem>
 */
class ShoppingListItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $items = [
            'Milk' => ['quantity' => 1, 'unit' => 'gallon', 'category' => 'Dairy'],
            'Bread' => ['quantity' => 1, 'unit' => 'loaf', 'category' => 'Bakery'],
            'Eggs' => ['quantity' => 12, 'unit' => 'count', 'category' => 'Dairy'],
            'Chicken' => ['quantity' => 2, 'unit' => 'lbs', 'category' => 'Meat'],
            'Apples' => ['quantity' => 5, 'unit' => 'count', 'category' => 'Produce'],
            'Bananas' => ['quantity' => 6, 'unit' => 'count', 'category' => 'Produce'],
            'Flour' => ['quantity' => 5, 'unit' => 'lbs', 'category' => 'Baking'],
            'Sugar' => ['quantity' => 4, 'unit' => 'lbs', 'category' => 'Baking'],
            'Coffee' => ['quantity' => 1, 'unit' => 'bag', 'category' => 'Beverages'],
            'Cereal' => ['quantity' => 1, 'unit' => 'box', 'category' => 'Breakfast'],
        ];

        $item = $this->faker->randomElement(array_keys($items));
        $details = $items[$item];

        return [
            'shopping_list_id' => ShoppingList::factory(),
            'ingredient_id' => null,
            'name' => $item,
            'quantity' => $details['quantity'],
            'unit' => $details['unit'],
            'category' => $details['category'],
            'is_custom' => true,
            'is_purchased' => $this->faker->boolean(20), // 20% chance to be purchased
        ];
    }

    /**
     * Indicate that the item is purchased.
     */
    public function purchased(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_purchased' => true,
        ]);
    }

    /**
     * Indicate that the item is not purchased.
     */
    public function notPurchased(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_purchased' => false,
        ]);
    }
}
