<?php

use App\Models\MealPlan;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->mealPlan = MealPlan::factory()->create([
        'user_id' => $this->user->id,
        'people_count' => 4,
    ]);
    $this->recipe = Recipe::factory()->create([
        'servings' => 6,
    ]);
    
    // Create the pivot record
    $this->mealPlan->recipes()->attach($this->recipe->id, [
        'scale_factor' => 1.5,
    ]);
    
    $this->mealPlanRecipe = $this->mealPlan->recipes()
        ->where('recipe_id', $this->recipe->id)
        ->first()
        ->pivot;
});

test('meal plan recipe has correct table name', function () {
    expect($this->mealPlanRecipe->getTable())->toBe('meal_plan_recipe');
});

test('meal plan recipe has incrementing id', function () {
    expect($this->mealPlanRecipe->getIncrementing())->toBeTrue();
});

test('meal plan recipe has correct attribute casting', function () {
    expect($this->mealPlanRecipe->getCasts())
        ->toBe([
            'id' => 'int',
            'scale_factor' => 'decimal:2',
            'servings_available' => 'decimal:2',
        ]);
});

test('meal plan recipe belongs to a meal plan', function () {
    expect($this->mealPlanRecipe->mealPlan)
        ->toBeInstanceOf(MealPlan::class)
        ->and($this->mealPlanRecipe->mealPlan->id)->toBe($this->mealPlan->id);
});

test('meal plan recipe belongs to a recipe', function () {
    expect($this->mealPlanRecipe->recipe)
        ->toBeInstanceOf(Recipe::class)
        ->and($this->mealPlanRecipe->recipe->id)->toBe($this->recipe->id);
});

test('meal plan recipe can calculate available servings', function () {
    // Arrange
    $this->mealPlanRecipe->calculateAvailableServings();

    // Assert
    // Formula: (recipe servings * scale factor) / people count
    // (6 * 1.5) / 4 = 2.25
    expect($this->mealPlanRecipe->servings_available)->toBe('2.25');
});

test('meal plan recipe handles zero people count when calculating servings', function () {
    // Arrange
    $this->mealPlan->update(['people_count' => 0]);
    $this->mealPlan->refresh();

    // Act
    $this->mealPlanRecipe->calculateAvailableServings();

    // Assert
    expect($this->mealPlanRecipe->servings_available)->toBe('0.00');
});

test('meal plan recipe handles missing recipe when calculating servings', function () {
    // Arrange
    $this->recipe->delete();

    // Act
    $this->mealPlanRecipe->calculateAvailableServings();

    // Assert
    expect($this->mealPlanRecipe->servings_available)->toBeNull();
});

test('meal plan recipe handles missing meal plan when calculating servings', function () {
    // Arrange
    $this->mealPlan->delete();

    // Act
    $this->mealPlanRecipe->calculateAvailableServings();

    // Assert
    expect($this->mealPlanRecipe->servings_available)->toBeNull();
});
