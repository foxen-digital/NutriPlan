<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

function setupTestData(): array
{
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'people_count' => 2]);
    $recipe = Recipe::factory()->create(['servings' => 6]);

    // Attach recipe to meal plan
    $mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);
    $mealPlanRecipe = $mealPlan->recipes()->first()->pivot;
    $mealPlanRecipe->servings_available = ($recipe->servings * $mealPlanRecipe->scale_factor) / $mealPlan->people_count;
    $mealPlanRecipe->save();

    $mealPlanDay = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 1]);

    $assignment = MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'servings' => 1.0,
        'to_cook' => false,
    ]);

    return compact('user', 'mealPlan', 'recipe', 'mealPlanDay', 'mealPlanRecipe', 'assignment');
}

test('store validates required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('meal-assignments.store'), []);

    $response->assertSessionHasErrors(['meal_plan_day_id', 'meal_plan_recipe_id', 'servings', 'to_cook']);
});

test('store validates servings range', function () {
    $data = setupTestData();
    $user = $data['user'];
    $mealPlanDay = $data['mealPlanDay'];
    $mealPlanRecipe = $data['mealPlanRecipe'];

    $response = $this->actingAs($user)->post(route('meal-assignments.store'), [
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'servings' => 0,
        'to_cook' => true,
    ]);

    $response->assertSessionHasErrors(['servings']);
});

test('update handles database transaction failure', function () {
    $data = setupTestData();
    $user = $data['user'];
    $assignment = $data['assignment'];

    // Mock DB transaction and simulate save failure
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();
    MealAssignment::saving(function () {
        throw new RuntimeException('Save failed');
    });

    $response = $this->actingAs($user)->put(route('meal-assignments.update', $assignment->id), [
        'servings' => 2.0,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['error' => 'Failed to update meal assignment. Please try again.']);
});

test('update validates servings range', function () {
    $data = setupTestData();
    $user = $data['user'];
    $assignment = $data['assignment'];

    $response = $this->actingAs($user)->put(route('meal-assignments.update', $assignment->id), [
        'servings' => 0,
    ]);

    $response->assertSessionHasErrors(['servings']);
});

test('destroy handles database transaction failure', function () {
    $data = setupTestData();
    $user = $data['user'];
    $assignment = $data['assignment'];

    // Mock DB transaction and simulate delete failure
    DB::shouldReceive('beginTransaction')->once();
    DB::shouldReceive('rollBack')->once();
    MealAssignment::deleting(function () {
        throw new RuntimeException('Delete failed');
    });

    $response = $this->actingAs($user)->delete(route('meal-assignments.destroy', $assignment->id));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['error' => 'Failed to remove meal assignment. Please try again.']);
});

test('toggle to cook handles exceptions', function () {
    $data = setupTestData();
    $user = $data['user'];
    $assignment = $data['assignment'];

    // Simulate a save exception via model saving event
    MealAssignment::saving(function () {
        throw new RuntimeException('Save failed');
    });

    $response = $this->actingAs($user)->post(route('meal-assignments.toggle-cook', $assignment->id));

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
        'message' => 'Failed to toggle cooking status.'
    ]);
});
