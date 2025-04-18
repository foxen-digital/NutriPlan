<?php

declare(strict_types=1);

use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->anotherUser = User::factory()->create();
    $this->mealPlan = MealPlan::factory()->create(['user_id' => $this->user->id]);
    $this->recipe = Recipe::factory()->create(['user_id' => $this->user->id]);
});

// Store Method Tests

test('authenticated user can add a recipe to their meal plan', function () {
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 1.5,
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Recipe added to meal plan successfully.'
        ]);

    $this->assertDatabaseHas('meal_plan_recipe', [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 1.5,
    ]);
});

test('authenticated user cannot add a recipe to another users meal plan', function () {
    $otherUserMealPlan = MealPlan::factory()->create(['user_id' => $this->anotherUser->id]);

    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $otherUserMealPlan->id,
            'recipe_id' => $this->recipe->id,
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('meal_plan_recipe', [
        'meal_plan_id' => $otherUserMealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
});

test('unauthenticated user cannot add a recipe to a meal plan', function () {
    $this->postJson(route('meal-plans.add-recipe'), [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
    ])
    ->assertUnauthorized();

    $this->assertDatabaseMissing('meal_plan_recipe', [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
});

test('adding recipe requires meal_plan_id', function () {
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'recipe_id' => $this->recipe->id,
        ])
        ->assertJsonValidationErrors(['meal_plan_id']);
});

test('adding recipe requires recipe_id', function () {
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
        ])
        ->assertJsonValidationErrors(['recipe_id']);
});

test('scale_factor must be numeric and within range', function () {
    // Too small
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 0.001,
        ])
        ->assertJsonValidationErrors(['scale_factor']);

    // Too large
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 150,
        ])
        ->assertJsonValidationErrors(['scale_factor']);

    // Not numeric
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 'abc',
        ])
        ->assertJsonValidationErrors(['scale_factor']);
});

test('adding a recipe that already exists in meal plan does not duplicate it', function () {
    // Add the recipe first time
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 1.0,
        ]);

    // Try to add the same recipe again
    $this->actingAs($this->user)
        ->postJson(route('meal-plans.add-recipe'), [
            'meal_plan_id' => $this->mealPlan->id,
            'recipe_id' => $this->recipe->id,
            'scale_factor' => 2.0, // Even with different scale factor
        ]);

    // Verify there's only one record
    $this->assertDatabaseCount('meal_plan_recipe', 1);

    // And it still has the original scale factor
    $this->assertDatabaseHas('meal_plan_recipe', [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
        'scale_factor' => 1.0,
    ]);
});

// Destroy Method Tests

test('authenticated user can remove a recipe from their meal plan', function () {
    // Add recipe to meal plan
    $this->mealPlan->recipes()->attach($this->recipe->id, [
        'scale_factor' => 1.0,
    ]);

    // Remove recipe
    $this->actingAs($this->user)
        ->delete(route('meal-plans.remove-recipe', [
            'mealPlan' => $this->mealPlan,
            'recipe' => $this->recipe,
        ]))
        ->assertRedirect();

    // Verify recipe was removed
    $this->assertDatabaseMissing('meal_plan_recipe', [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
});

test('authenticated user cannot remove a recipe from another users meal plan', function () {
    $otherUserMealPlan = MealPlan::factory()->create(['user_id' => $this->anotherUser->id]);
    $otherUserMealPlan->recipes()->attach($this->recipe->id);

    $this->actingAs($this->user)
        ->delete(route('meal-plans.remove-recipe', [
            'mealPlan' => $otherUserMealPlan,
            'recipe' => $this->recipe,
        ]))
        ->assertForbidden();

    $this->assertDatabaseHas('meal_plan_recipe', [
        'meal_plan_id' => $otherUserMealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
});

test('unauthenticated user cannot remove a recipe from a meal plan', function () {
    $this->mealPlan->recipes()->attach($this->recipe->id);

    $this->delete(route('meal-plans.remove-recipe', [
        'mealPlan' => $this->mealPlan,
        'recipe' => $this->recipe,
    ]))
    ->assertRedirect(route('login'));

    $this->assertDatabaseHas('meal_plan_recipe', [
        'meal_plan_id' => $this->mealPlan->id,
        'recipe_id' => $this->recipe->id,
    ]);
});
