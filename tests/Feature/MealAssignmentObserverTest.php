<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\User;
use App\Services\ShoppingListSyncService;
use Illuminate\Support\Facades\App;

function setupMealAssignmentObserverTestData(): array
{
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'people_count' => 2]);
    $recipe = Recipe::factory()->create(['servings' => 6]);

    // Attach recipe to meal plan to create the MealPlanRecipe pivot record
    $mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);
    $mealPlanRecipe = $mealPlan->recipes()->first()->pivot;
    $mealPlanRecipe->servings_available = ($recipe->servings * $mealPlanRecipe->scale_factor) / $mealPlan->people_count;
    $mealPlanRecipe->save();

    $mealPlanDay = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 1]);

    return compact('user', 'mealPlan', 'recipe', 'mealPlanDay', 'mealPlanRecipe');
}

test('observer triggers ShoppingListSyncService when meal assignment is created with to_cook true', function () {
    $data = setupMealAssignmentObserverTestData();
    /** @var MealPlanRecipe $mealPlanRecipe */
    $mealPlanRecipe = $data['mealPlanRecipe'];
    /** @var MealPlanDay $mealPlanDay */
    $mealPlanDay = $data['mealPlanDay'];

    // Mock the ShoppingListSyncService
    $mock = \Mockery::mock(ShoppingListSyncService::class);
    $mock->expects('syncNewMeal')
        ->once()
        ->with(\Mockery::on(function (MealAssignment $mealAssignment) use ($mealPlanDay, $mealPlanRecipe) {
            return $mealAssignment->meal_plan_day_id === $mealPlanDay->id
                && $mealAssignment->meal_plan_recipe_id === $mealPlanRecipe->id
                && $mealAssignment->to_cook === true;
        }));

    App::instance(ShoppingListSyncService::class, $mock);

    // Adjust available servings before creating assignment
    $mealPlanRecipe->servings_available -= 1.0;
    $mealPlanRecipe->save();

    // Create a meal assignment with to_cook = true
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'servings' => 1.0,
        'to_cook' => true,
    ]);
});

test('observer does not trigger service when to_cook is false', function () {
    $data = setupMealAssignmentObserverTestData();
    /** @var MealPlanRecipe $mealPlanRecipe */
    $mealPlanRecipe = $data['mealPlanRecipe'];
    /** @var MealPlanDay $mealPlanDay */
    $mealPlanDay = $data['mealPlanDay'];

    // Mock the ShoppingListSyncService - should NOT be called
    $mock = \Mockery::mock(ShoppingListSyncService::class);
    $mock->expects('syncNewMeal')->never();

    App::instance(ShoppingListSyncService::class, $mock);

    // Adjust available servings before creating assignment
    $mealPlanRecipe->servings_available -= 1.0;
    $mealPlanRecipe->save();

    // Create a meal assignment with to_cook = false
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'servings' => 1.0,
        'to_cook' => false,
    ]);
});

test('observer does not trigger service on meal assignment update', function () {
    $data = setupMealAssignmentObserverTestData();
    /** @var MealPlanRecipe $mealPlanRecipe */
    $mealPlanRecipe = $data['mealPlanRecipe'];
    /** @var MealPlanDay $mealPlanDay */
    $mealPlanDay = $data['mealPlanDay'];

    // Create without triggering observer to fully isolate the update assertion
    $assignment = MealAssignment::withoutObservers(function () use ($mealPlanDay, $mealPlanRecipe) {
        $mealPlanRecipe->servings_available -= 1.0;
        $mealPlanRecipe->save();

        return MealAssignment::factory()->create([
            'meal_plan_day_id' => $mealPlanDay->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'servings' => 1.0,
            'to_cook' => true,
        ]);
    });

    // Now mock the service for the update - should NOT be called
    $mock = \Mockery::mock(ShoppingListSyncService::class);
    $mock->expects('syncNewMeal')->never();

    App::instance(ShoppingListSyncService::class, $mock);

    // Update the meal assignment
    $assignment->update(['servings' => 2.0]);
});

test('observer does not trigger service on meal assignment delete', function () {
    $data = setupMealAssignmentObserverTestData();
    /** @var MealPlanRecipe $mealPlanRecipe */
    $mealPlanRecipe = $data['mealPlanRecipe'];
    /** @var MealPlanDay $mealPlanDay */
    $mealPlanDay = $data['mealPlanDay'];

    // Create without triggering observer to fully isolate the delete assertion
    $assignment = MealAssignment::withoutObservers(function () use ($mealPlanDay, $mealPlanRecipe) {
        $mealPlanRecipe->servings_available -= 1.0;
        $mealPlanRecipe->save();

        return MealAssignment::factory()->create([
            'meal_plan_day_id' => $mealPlanDay->id,
            'meal_plan_recipe_id' => $mealPlanRecipe->id,
            'servings' => 1.0,
            'to_cook' => true,
        ]);
    });

    // Now mock the service for the delete - should NOT be called
    $mock = \Mockery::mock(ShoppingListSyncService::class);
    $mock->expects('syncNewMeal')->never();

    App::instance(ShoppingListSyncService::class, $mock);

    // Delete the meal assignment
    $assignment->delete();
});

test('observer loads relationships before calling service', function () {
    $data = setupMealAssignmentObserverTestData();
    /** @var MealPlanRecipe $mealPlanRecipe */
    $mealPlanRecipe = $data['mealPlanRecipe'];
    /** @var MealPlanDay $mealPlanDay */
    $mealPlanDay = $data['mealPlanDay'];

    // Mock the ShoppingListSyncService and verify relationships are loaded
    $mock = \Mockery::mock(ShoppingListSyncService::class);
    $mock->expects('syncNewMeal')
        ->once()
        ->with(\Mockery::on(function (MealAssignment $mealAssignment) {
            // Verify the required relationships are loaded
            $hasMealPlanDay = $mealAssignment->relationLoaded('mealPlanDay');
            $hasMealPlanRecipe = $mealAssignment->relationLoaded('mealPlanRecipe');

            if ($hasMealPlanDay) {
                $hasMealPlan = $mealAssignment->mealPlanDay->relationLoaded('mealPlan');
            } else {
                $hasMealPlan = false;
            }

            $hasRecipe = false;
            $hasIngredients = false;
            if ($hasMealPlanRecipe) {
                $hasRecipe = $mealAssignment->mealPlanRecipe->relationLoaded('recipe');
                if ($hasRecipe) {
                    $hasIngredients = $mealAssignment->mealPlanRecipe->recipe->relationLoaded('ingredients');
                }
            }

            return $hasMealPlanDay && $hasMealPlanRecipe && $hasMealPlan && $hasRecipe && $hasIngredients;
        }));

    App::instance(ShoppingListSyncService::class, $mock);

    // Adjust available servings before creating assignment
    $mealPlanRecipe->servings_available -= 1.0;
    $mealPlanRecipe->save();

    // Create a meal assignment with to_cook = true
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay->id,
        'meal_plan_recipe_id' => $mealPlanRecipe->id,
        'servings' => 1.0,
        'to_cook' => true,
    ]);
});
