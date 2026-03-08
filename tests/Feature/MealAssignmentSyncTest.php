<?php

declare(strict_types=1);

use App\Events\ShoppingListUpdated;
use App\Models\Ingredient;
use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Support\Facades\Event;

/**
 * Set up base test data for sync integration tests.
 * Does NOT create a MealAssignment (that is the "act" step in each test).
 *
 * @return array{user: User, mealPlan: MealPlan, recipe: Recipe, mealPlanDay: MealPlanDay, mealPlanRecipe: MealPlanRecipe}
 */
function setupSyncIntegrationTestData(): array
{
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'people_count' => 2]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id, 'servings' => 4]);

    $mealPlan->recipes()->attach($recipe->id, ['scale_factor' => 1.0]);
    $mealPlanRecipe = MealPlanRecipe::where('meal_plan_id', $mealPlan->id)
        ->where('recipe_id', $recipe->id)
        ->first();

    $mealPlanDay = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id]);

    return compact('user', 'mealPlan', 'recipe', 'mealPlanDay', 'mealPlanRecipe');
}

// =============================================================================
// AC4: Full synchronization flow integration tests
// =============================================================================

test('adding a to_cook meal updates the linked shopping list with ingredients', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Butter']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 50.0, 'unit' => 'g']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Act: create meal assignment — triggers Observer → Service → Job inline
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    // Assert: shopping list now has the ingredient
    $items = $shoppingList->fresh()->items;
    expect($items)->toHaveCount(1)
        ->and($items->first()->name)->toBe('Butter')
        ->and((float) $items->first()->quantity)->toBe(50.0)
        ->and($items->first()->unit)->toBe('g');
});

test('adding a to_cook meal dispatches ShoppingListUpdated event for the linked list', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Sugar']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 100.0, 'unit' => 'g']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    Event::assertDispatched(ShoppingListUpdated::class, function ($event) use ($shoppingList, $data) {
        return $event->shoppingListId === $shoppingList->id
            && $event->userId === $data['user']->id;
    });
});

test('adding a meal to a plan with no linked shopping lists creates no items', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 200.0, 'unit' => 'g']);

    // No shopping list linked to this meal plan

    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    // Assert: no shopping list items exist at all
    expect(\App\Models\ShoppingListItem::count())->toBe(0);
    Event::assertNotDispatched(ShoppingListUpdated::class);
});

test('adding a meal marked as leftover (to_cook=false) creates no items', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Eggs']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 3.0, 'unit' => 'count']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Act: meal marked as leftover (to_cook = false)
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => false,
        'servings' => 1.0,
    ]);

    // Assert: shopping list untouched
    expect($shoppingList->fresh()->items)->toHaveCount(0);
    Event::assertNotDispatched(ShoppingListUpdated::class);
});

test('multiple shopping lists linked to same meal plan all receive ingredient updates', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Milk']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 250.0, 'unit' => 'ml']);

    $list1 = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);
    $list2 = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);
    $list3 = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    // Assert: all three lists received the ingredient independently
    foreach ([$list1, $list2, $list3] as $list) {
        $items = $list->fresh()->items;
        expect($items)->toHaveCount(1)
            ->and($items->first()->name)->toBe('Milk')
            ->and((float) $items->first()->quantity)->toBe(250.0);
    }

    // One event dispatched per list
    Event::assertDispatchedTimes(ShoppingListUpdated::class, 3);
});

test('two meals added to same plan accumulate ingredients on the shopping list', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();

    // Second recipe and meal plan recipe
    $recipe2 = Recipe::factory()->create(['user_id' => $data['user']->id, 'servings' => 4]);
    $data['mealPlan']->recipes()->attach($recipe2->id, ['scale_factor' => 1.0]);
    $mealPlanRecipe2 = MealPlanRecipe::where('meal_plan_id', $data['mealPlan']->id)
        ->where('recipe_id', $recipe2->id)
        ->first();

    // Both recipes share one ingredient (flour) at different amounts, and each has a unique one
    $flour = Ingredient::factory()->create(['name' => 'Flour']);
    $butter = Ingredient::factory()->create(['name' => 'Butter']);
    $sugar = Ingredient::factory()->create(['name' => 'Sugar']);

    $data['recipe']->ingredients()->attach([
        $flour->id => ['amount' => 200.0, 'unit' => 'g'],
        $butter->id => ['amount' => 100.0, 'unit' => 'g'],
    ]);
    $recipe2->ingredients()->attach([
        $flour->id => ['amount' => 150.0, 'unit' => 'g'],
        $sugar->id => ['amount' => 80.0, 'unit' => 'g'],
    ]);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Act: add two meals
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    $mealPlanDay2 = MealPlanDay::factory()->create(['meal_plan_id' => $data['mealPlan']->id]);
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $mealPlanDay2->id,
        'meal_plan_recipe_id' => $mealPlanRecipe2->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    // Assert: flour incremented (200 + 150), butter and sugar created independently
    $items = $shoppingList->fresh()->items;
    expect($items)->toHaveCount(3);

    $flourItem = $items->firstWhere('ingredient_id', $flour->id);
    $butterItem = $items->firstWhere('ingredient_id', $butter->id);
    $sugarItem = $items->firstWhere('ingredient_id', $sugar->id);

    expect($flourItem)->not->toBeNull()
        ->and((float) $flourItem->quantity)->toBe(350.0); // 200 + 150
    expect($butterItem)->not->toBeNull()
        ->and((float) $butterItem->quantity)->toBe(100.0);
    expect($sugarItem)->not->toBeNull()
        ->and((float) $sugarItem->quantity)->toBe(80.0);
});

test('shopping lists belonging to other meal plans are not updated', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Cheese']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 50.0, 'unit' => 'g']);

    // List linked to our meal plan — should be updated
    $linkedList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // List NOT linked to our meal plan — should NOT be updated
    $otherMealPlan = MealPlan::factory()->create(['user_id' => $data['user']->id]);
    $unlinkedList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $otherMealPlan->id,
    ]);

    MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    expect($linkedList->fresh()->items)->toHaveCount(1);
    expect($unlinkedList->fresh()->items)->toHaveCount(0);
});

// =============================================================================
// AC5: Addition-only constraint edge case tests
// =============================================================================

test('updating an existing meal assignment does not modify shopping list items', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Salt']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 10.0, 'unit' => 'g']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Create the assignment — adds the ingredient
    $assignment = MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    expect($shoppingList->fresh()->items)->toHaveCount(1);
    Event::assertDispatchedTimes(ShoppingListUpdated::class, 1);

    // Act: update the assignment (servings change) — observer only fires on 'created'
    $assignment->update(['servings' => 2.0]);

    // Assert: items NOT duplicated or modified
    $items = $shoppingList->fresh()->items;
    expect($items)->toHaveCount(1)
        ->and((float) $items->first()->quantity)->toBe(10.0); // unchanged
    Event::assertDispatchedTimes(ShoppingListUpdated::class, 1); // no additional event
});

test('deleting a meal assignment does not remove ingredients from the shopping list', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Pepper']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 5.0, 'unit' => 'g']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Create the assignment — adds the ingredient
    $assignment = MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    expect($shoppingList->fresh()->items)->toHaveCount(1);

    // Act: delete the assignment — addition-only means nothing is removed
    $assignment->delete();

    // Assert: ingredients remain on the shopping list
    $items = $shoppingList->fresh()->items;
    expect($items)->toHaveCount(1)
        ->and($items->first()->name)->toBe('Pepper')
        ->and((float) $items->first()->quantity)->toBe(5.0);
});

test('toggling to_cook from true to false does not remove ingredients from the shopping list', function () {
    config(['queue.default' => 'sync']);
    Event::fake([ShoppingListUpdated::class]);

    $data = setupSyncIntegrationTestData();
    $ingredient = Ingredient::factory()->create(['name' => 'Onion']);
    $data['recipe']->ingredients()->attach($ingredient->id, ['amount' => 2.0, 'unit' => 'count']);

    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $data['user']->id,
        'meal_plan_id' => $data['mealPlan']->id,
    ]);

    // Create the assignment with to_cook=true — adds the ingredient
    $assignment = MealAssignment::factory()->create([
        'meal_plan_day_id' => $data['mealPlanDay']->id,
        'meal_plan_recipe_id' => $data['mealPlanRecipe']->id,
        'to_cook' => true,
        'servings' => 1.0,
    ]);

    expect($shoppingList->fresh()->items)->toHaveCount(1);
    Event::assertDispatchedTimes(ShoppingListUpdated::class, 1);

    // Act: toggle to_cook off — observer only fires on 'created', not 'updated'
    $assignment->update(['to_cook' => false]);

    // Assert: ingredients remain untouched (addition-only)
    $items = $shoppingList->fresh()->items;
    expect($items)->toHaveCount(1)
        ->and($items->first()->name)->toBe('Onion')
        ->and((float) $items->first()->quantity)->toBe(2.0);
    Event::assertDispatchedTimes(ShoppingListUpdated::class, 1); // no additional removal event
});
