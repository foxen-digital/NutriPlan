<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Assuming RefreshDatabase is used globally via tests/Pest.php

beforeEach(function () {
    // Create a user
    $this->user = User::factory()->create();

    // Create a meal plan owned by the user
    $this->mealPlan = MealPlan::factory()->recycle($this->user)->create();

    // Create a meal plan day for this plan
    $this->mealPlanDay = MealPlanDay::factory()->recycle($this->mealPlan)->create(['day_number' => 1]);

    // Create some recipes and link them to the meal plan
    $this->recipes = Recipe::factory(3)->create();
    $this->mealPlanRecipes = collect();
    foreach ($this->recipes as $recipe) {
        $this->mealPlanRecipes->push(
            MealPlanRecipe::factory()
                ->recycle($this->mealPlan)
                ->recycle($recipe)
                ->create()
        );
    }

    // Create meal assignments for the day using the linked recipes
    $this->assignments = collect();
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $this->assignments->push(
            MealAssignment::factory()
                ->recycle($this->mealPlanDay)
                ->recycle($mealPlanRecipe)
                ->create(['order' => $index]) // Initial order
        );
    }

    // Define the route for convenience
    $this->route = route('meal-plan-days.assignments.reorder', ['meal_plan_day' => $this->mealPlanDay->id]);
});

test('authenticated owner can reorder meal assignments', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments
    $currentIds = collect($assignments)->pluck('id')->toArray();
    $shuffledIds = $currentIds;
    shuffle($shuffledIds);

    // Ensure the order is actually different
    if ($shuffledIds === $currentIds) {
        [$shuffledIds[0], $shuffledIds[1]] = [$shuffledIds[1], $shuffledIds[0]]; // Simple swap if shuffle didn't change order
    }

    // Act: Send the PATCH request with the new order
    $response = $this->actingAs($this->user)
        ->patch($this->route, ['assignment_ids' => $shuffledIds]);

    // Assert: Check for successful redirect back (common Inertia pattern)
    $response->assertRedirect();
    $response->assertSessionHas('success'); // Check for the success flash message

    // Assert: Check database for updated order
    foreach ($shuffledIds as $index => $id) {
        $this->assertDatabaseHas('meal_assignments', [
            'id' => $id,
            'order' => $index,
        ]);
    }
});

test('cannot reorder assignments with an ID not belonging to the day', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments
    $currentIds = collect($assignments)->pluck('id')->toArray();

    // Create a different meal plan and day to avoid uniqueness issues
    $otherMealPlan = MealPlan::factory()->recycle($this->user)->create();
    $otherMealPlanDay = MealPlanDay::factory()
        ->withDayNumber(20) // Use an explicit day number that's unlikely to conflict
        ->create([
            'meal_plan_id' => $otherMealPlan->id
        ]);

    // Create an assignment belonging to this other day, using one of the existing mealPlanRecipes
    $otherAssignment = MealAssignment::factory()
        ->create([
            'meal_plan_day_id' => $otherMealPlanDay->id,
            'meal_plan_recipe_id' => $this->mealPlanRecipes->first()->id,
            'order' => 0
        ]);

    // Prepare the list with one ID replaced by the one from the other day
    $invalidIds = $currentIds;
    $invalidIds[1] = $otherAssignment->id;

    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
        ->patch($this->route, ['assignment_ids' => $invalidIds]);

    // Assert: Check for validation error (the controller should detect this ID is not for $this->mealPlanDay)
    $response->assertSessionHasErrors('assignment_ids');
});

test('cannot reorder assignments with an incomplete list for the day', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments and remove one
    $incompleteIds = collect($assignments)->pluck('id')->slice(0, -1)->toArray();

    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
        ->patch($this->route, ['assignment_ids' => $incompleteIds]);

    // Assert: Check for validation error
    $response->assertSessionHasErrors('assignment_ids');
});

test('unauthenticated user cannot reorder assignments', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments
    $ids = collect($assignments)->pluck('id')->toArray();

    // Act: Send the PATCH request without authentication
    $response = $this->patch($this->route, ['assignment_ids' => $ids]);

    // Assert: Check for redirect to login (assuming default behavior)
    $response->assertRedirect(route('login'));
});

test('user cannot reorder assignments on a meal plan they do not own', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments
    $ids = collect($assignments)->pluck('id')->toArray();

    // Arrange: Create another user
    $otherUser = User::factory()->create();

    // Act: Attempt request as the other user
    $response = $this->actingAs($otherUser)
        ->patch($this->route, ['assignment_ids' => $ids]);

    // Assert: Check for forbidden status
    $response->assertForbidden();
});

test('validation fails if assignment_ids is null', function () {
    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
       ->patch($this->route, ['assignment_ids' => null]);

    // Assert: Check for validation error
    $response->assertSessionHasErrors('assignment_ids');
});

test('validation fails if assignment_ids is not an array', function () {
    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
       ->patch($this->route, ['assignment_ids' => 'string']);

    // Assert: Check for validation error
    $response->assertSessionHasErrors('assignment_ids');
});

test('validation fails if assignment_ids is an empty array', function () {
    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
       ->patch($this->route, ['assignment_ids' => []]);

    // Assert: Check for validation error
    $response->assertSessionHasErrors('assignment_ids');
});

test('validation fails if assignment_ids contains non-integer', function () {
    // Create meal assignments for the day directly in this test
    $assignments = [];
    foreach ($this->mealPlanRecipes as $index => $mealPlanRecipe) {
        $assignments[] = MealAssignment::factory()
            ->create([
                'meal_plan_day_id' => $this->mealPlanDay->id,
                'meal_plan_recipe_id' => $mealPlanRecipe->id,
                'order' => $index
            ]);
    }

    // Get the IDs directly from our created assignments
    $ids = collect($assignments)->pluck('id')->toArray();

    // Replace one ID with a non-integer
    $ids[1] = 'not-an-integer'; // Replace one with invalid data

    // Act: Send the PATCH request
    $response = $this->actingAs($this->user)
        ->patch($this->route, ['assignment_ids' => $ids]);

    // Assert: Check for validation error ('integer' rule)
    $response->assertSessionHasErrors('assignment_ids.1'); // Pest can assert specific array index errors
});

test('debug: check meal plan day assignments', function () {
    // Check what assignments are in the database for this day
    $dbAssignments = DB::table('meal_assignments')
        ->where('meal_plan_day_id', $this->mealPlanDay->id)
        ->get();

    dump('Assignments in DB:', $dbAssignments->toArray());
    dump('Assignment IDs in beforeEach:', $this->assignments->pluck('id')->toArray());

    // This test doesn't assert anything, it's for debugging only
    expect(true)->toBeTrue();
});
