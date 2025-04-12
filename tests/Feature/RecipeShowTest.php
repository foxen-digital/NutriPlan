<?php

declare(strict_types=1);

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('recipe show page displays ingredients with descriptions', function () {
    // Create a user and recipe
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Recipe',
        'instructions' => "Step 1\nStep 2\nStep 3",
        'is_public' => true,
    ]);

    // Create ingredients
    $olive_oil = Ingredient::factory()->create(['name' => 'olive oil']);
    $garlic = Ingredient::factory()->create(['name' => 'garlic']);
    $salt = Ingredient::factory()->create(['name' => 'salt']);

    // Attach ingredients with pivot data including descriptions
    $recipe->ingredients()->attach([
        $olive_oil->id => [
            'amount' => 2,
            'unit' => 'tbsp',
            'description' => 'extra virgin olive oil',
        ],
        $garlic->id => [
            'amount' => 3,
            'unit' => 'clove',
            'description' => 'garlic, minced',
        ],
        $salt->id => [
            'amount' => 0,
            'unit' => 'pinch',
            'description' => 'salt, to taste',
        ],
    ]);

    // Visit the recipe page
    $response = $this->actingAs($user)
        ->get(route('recipes.show', $recipe->slug));

    $response->assertStatus(200);

    // Assert that Inertia data includes the ingredients with descriptions
    $response->assertInertia(function (Assert $page) use ($olive_oil, $garlic, $salt) {
        $page->component('Recipes/Show')
            ->has('recipe.ingredients', 3)
            ->where('recipe.ingredients.0.pivot.description', 'extra virgin olive oil')
            ->where('recipe.ingredients.1.pivot.description', 'garlic, minced')
            ->where('recipe.ingredients.2.pivot.description', 'salt, to taste');
    });
});

test('recipe show page falls back to ingredient name when description is missing', function () {
    // Create a user and recipe
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Recipe',
        'is_public' => true,
    ]);

    // Create an ingredient with no description in pivot
    $ingredient = Ingredient::factory()->create(['name' => 'sugar']);

    // Attach ingredient without a description
    $recipe->ingredients()->attach([
        $ingredient->id => [
            'amount' => 1,
            'unit' => 'cup',
            // No description provided
        ],
    ]);

    // Visit the recipe page
    $response = $this->actingAs($user)
        ->get(route('recipes.show', $recipe->slug));

    $response->assertStatus(200);

    // Check the ingredient name is visible
    $response->assertInertia(function (Assert $page) {
        $page->component('Recipes/Show')
            ->has('recipe.ingredients', 1)
            ->where('recipe.ingredients.0.name', 'sugar');
    });
});

test('recipe show page handles null ingredient amounts correctly', function () {
    // Create a user and recipe
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Recipe',
        'is_public' => true,
    ]);

    // Create an ingredient with null amount
    $ingredient = Ingredient::factory()->create(['name' => 'black pepper']);

    // Attach ingredient with null amount
    $recipe->ingredients()->attach([
        $ingredient->id => [
            'amount' => null,
            'unit' => 'pinch',
            'description' => 'freshly ground black pepper',
        ],
    ]);

    // Visit the recipe page
    $response = $this->actingAs($user)
        ->get(route('recipes.show', $recipe->slug));

    $response->assertStatus(200);

    // Check the ingredient data is correct
    $response->assertInertia(function (Assert $page) {
        $page->component('Recipes/Show')
            ->has('recipe.ingredients', 1)
            ->where('recipe.ingredients.0.pivot.amount', null)
            ->where('recipe.ingredients.0.pivot.unit', 'pinch')
            ->where('recipe.ingredients.0.pivot.description', 'freshly ground black pepper');
    });
});
