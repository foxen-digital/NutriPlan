<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\MealPlan;
use App\Services\ShoppingListService;
use Illuminate\Support\Carbon;
use ReflectionClass;
use ReflectionMethod;
use App\Models\User;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\MealPlanRecipe;
use App\Models\MealPlanDay;
use App\Models\MealAssignment;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;

// Helper function to invoke private method via reflection
function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
{
    $reflectionClass = new ReflectionClass($object);
    $method = $reflectionClass->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $parameters);
}

beforeEach(function () {
    $this->service = new ShoppingListService();
});

test('calculate period dates returns correct dates for full period', function () {
    $mealPlan = new MealPlan();
    $mealPlan->start_date = Carbon::parse('2023-08-01');
    $mealPlan->duration = 7;

    $result = invokePrivateMethod($this->service, 'calculatePeriodDates', [$mealPlan, 'full']);

    expect($result['start_date']->format('Y-m-d'))->toBe('2023-08-01')
        ->and($result['end_date']->format('Y-m-d'))->toBe('2023-08-07');
});

test('calculate period dates returns correct dates for week1', function () {
    $mealPlan = new MealPlan();
    $mealPlan->start_date = Carbon::parse('2023-08-01');
    $mealPlan->duration = 14;

    $result = invokePrivateMethod($this->service, 'calculatePeriodDates', [$mealPlan, 'week1']);

    expect($result['start_date']->format('Y-m-d'))->toBe('2023-08-01')
        ->and($result['end_date']->format('Y-m-d'))->toBe('2023-08-07');
});

test('calculate period dates returns correct dates for week2', function () {
    $mealPlan = new MealPlan();
    $mealPlan->start_date = Carbon::parse('2023-08-01');
    $mealPlan->duration = 14;

    $result = invokePrivateMethod($this->service, 'calculatePeriodDates', [$mealPlan, 'week2']);

    expect($result['start_date']->format('Y-m-d'))->toBe('2023-08-08')
        ->and($result['end_date']->format('Y-m-d'))->toBe('2023-08-14');
});

// Helper function to create basic meal plan data for testing generation
function setupMealPlanForGeneration(): array
{
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create([
        'user_id' => $user->id,
        'start_date' => '2023-08-01',
        'duration' => 14, // 2 weeks
        'people_count' => 2,
    ]);

    // Ingredients
    $flour = Ingredient::factory()->create(['name' => 'Flour']); // With unit
    $sugar = Ingredient::factory()->create(['name' => 'Sugar']); // With unit, different assignment
    $salt = Ingredient::factory()->create(['name' => 'Salt']);  // No unit, no quantity initially
    $eggs = Ingredient::factory()->create(['name' => 'Eggs']);  // No unit, but will get quantity

    // Recipes
    $recipe1 = Recipe::factory()->create(['user_id' => $user->id, 'servings' => 4]);
    $recipe1->ingredients()->attach($flour, ['amount' => 2, 'unit' => 'cup']);
    $recipe1->ingredients()->attach($sugar, ['amount' => 1, 'unit' => 'cup']);
    $recipe1->ingredients()->attach($salt, ['amount' => null, 'unit' => 'pinch']); // Null amount

    $recipe2 = Recipe::factory()->create(['user_id' => $user->id, 'servings' => 2]);
    $recipe2->ingredients()->attach($flour, ['amount' => 1, 'unit' => 'cup']); // Same ingredient, same unit
    $recipe2->ingredients()->attach($eggs, ['amount' => 3, 'unit' => null]); // No unit initially

    // Meal Plan Recipes (Pivot)
    $mpRecipe1 = $mealPlan->recipes()->attach($recipe1->id, ['scale_factor' => 1.0]);
    $mpRecipe1Pivot = MealPlanRecipe::where('meal_plan_id', $mealPlan->id)->where('recipe_id', $recipe1->id)->first();
    $mpRecipe1Pivot->calculateAvailableServings();
    $mpRecipe1Pivot->save();

    $mpRecipe2 = $mealPlan->recipes()->attach($recipe2->id, ['scale_factor' => 1.0]);
    $mpRecipe2Pivot = MealPlanRecipe::where('meal_plan_id', $mealPlan->id)->where('recipe_id', $recipe2->id)->first();
    $mpRecipe2Pivot->calculateAvailableServings();
    $mpRecipe2Pivot->save();

    // Days
    $day1 = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 1]); // Week 1
    $day8 = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 8]); // Week 2

    // Assignments
    // Recipe 1 on Day 1 (Week 1), to cook
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $day1->id,
        'meal_plan_recipe_id' => $mpRecipe1Pivot->id,
        'servings' => 1,
        'to_cook' => true,
    ]);
    // Recipe 2 on Day 8 (Week 2), to cook
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $day8->id,
        'meal_plan_recipe_id' => $mpRecipe2Pivot->id,
        'servings' => 1,
        'to_cook' => true,
    ]);
    // Recipe 1 again on Day 8 (Week 2), NOT to cook (should be ignored)
    MealAssignment::factory()->create([
        'meal_plan_day_id' => $day8->id,
        'meal_plan_recipe_id' => $mpRecipe1Pivot->id,
        'servings' => 1,
        'to_cook' => false,
    ]);

    return compact('user', 'mealPlan', 'flour', 'sugar', 'salt', 'eggs');
}

test('generateFromMealPlan creates list for full period', function () {
    $data = setupMealPlanForGeneration();
    $mealPlan = $data['mealPlan'];
    $flour = $data['flour'];
    $sugar = $data['sugar'];
    $salt = $data['salt'];
    $eggs = $data['eggs'];

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Full List', 'full');

    expect($shoppingList)->not->toBeNull()
        ->and($shoppingList->name)->toBe('Full List')
        ->and($shoppingList->start_date->format('Y-m-d'))->toBe('2023-08-01')
        ->and($shoppingList->end_date->format('Y-m-d'))->toBe('2023-08-14');

    // Check items - both recipes included
    expect($shoppingList->items)->toHaveCount(4);
    // Flour (2 from R1 + 1 from R2)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $flour->id)->quantity)->toBe(3.0);
    // Sugar (1 from R1)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $sugar->id)->quantity)->toBe(1.0);
    // Salt (null amount from R1)
    $saltItem = $shoppingList->items->firstWhere('ingredient_id', $salt->id);
    expect($saltItem->quantity)->toBeNull()->and($saltItem->unit)->toBe('pinch');
    // Eggs (3 from R2)
    $eggItem = $shoppingList->items->firstWhere('ingredient_id', $eggs->id);
    expect((float) $eggItem->quantity)->toBe(3.0)->and($eggItem->unit)->toBeNull();
});

test('generateFromMealPlan creates list for week 1 only', function () {
    $data = setupMealPlanForGeneration();
    $mealPlan = $data['mealPlan'];
    $flour = $data['flour'];
    $sugar = $data['sugar'];
    $salt = $data['salt'];

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Week 1 List', 'week1');

    expect($shoppingList)->not->toBeNull()
        ->and($shoppingList->name)->toBe('Week 1 List')
        ->and($shoppingList->start_date->format('Y-m-d'))->toBe('2023-08-01')
        ->and($shoppingList->end_date->format('Y-m-d'))->toBe('2023-08-07');

    // Check items - only recipe 1 included
    expect($shoppingList->items)->toHaveCount(3); // Flour, Sugar, Salt
    expect($shoppingList->items->contains('name', 'Eggs'))->toBeFalse();
    // Flour (2 from R1)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $flour->id)->quantity)->toBe(2.0);
    // Sugar (1 from R1)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $sugar->id)->quantity)->toBe(1.0);
    // Salt (null amount from R1)
    expect($shoppingList->items->firstWhere('ingredient_id', $salt->id)->quantity)->toBeNull();
});

test('generateFromMealPlan creates list for week 2 only', function () {
    $data = setupMealPlanForGeneration();
    $mealPlan = $data['mealPlan'];
    $flour = $data['flour'];
    $eggs = $data['eggs'];

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Week 2 List', 'week2');

    expect($shoppingList)->not->toBeNull()
        ->and($shoppingList->name)->toBe('Week 2 List')
        ->and($shoppingList->start_date->format('Y-m-d'))->toBe('2023-08-08')
        ->and($shoppingList->end_date->format('Y-m-d'))->toBe('2023-08-14');

    // Check items - only recipe 2 included
    expect($shoppingList->items)->toHaveCount(2); // Flour, Eggs
    expect($shoppingList->items->contains('name', 'Sugar'))->toBeFalse();
    expect($shoppingList->items->contains('name', 'Salt'))->toBeFalse();
    // Flour (1 from R2)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $flour->id)->quantity)->toBe(1.0);
    // Eggs (3 from R2)
    expect((float) $shoppingList->items->firstWhere('ingredient_id', $eggs->id)->quantity)->toBe(3.0);
});

test('generateFromMealPlan handles ingredient without quantity correctly', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'start_date' => now(), 'duration' => 7]);
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);
    $ingredient = Ingredient::factory()->create(['name' => 'Bay Leaf']);
    $recipe->ingredients()->attach($ingredient, ['amount' => null, 'unit' => null]); // No amount, no unit

    $mpRecipePivot = MealPlanRecipe::create(['meal_plan_id' => $mealPlan->id, 'recipe_id' => $recipe->id]);
    $day = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 1]);
    MealAssignment::factory()->create(['meal_plan_day_id' => $day->id, 'meal_plan_recipe_id' => $mpRecipePivot->id, 'to_cook' => true]);

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test', 'full');

    expect($shoppingList->items)->toHaveCount(1);
    $item = $shoppingList->items->first();
    expect($item->name)->toBe('Bay Leaf')
        ->and($item->quantity)->toBeNull()
        ->and($item->unit)->toBeNull();
});

test('generateFromMealPlan ignores ingredient without quantity if version with quantity exists', function () {
    $user = User::factory()->create();
    $mealPlan = MealPlan::factory()->create(['user_id' => $user->id, 'start_date' => now(), 'duration' => 7]);
    $ingredient = Ingredient::factory()->create(['name' => 'Garlic']);

    // Recipe 1: Garlic with quantity
    $recipe1 = Recipe::factory()->create(['user_id' => $user->id]);
    $recipe1->ingredients()->attach($ingredient, ['amount' => 2, 'unit' => 'cloves']);
    $mpRecipe1Pivot = MealPlanRecipe::create(['meal_plan_id' => $mealPlan->id, 'recipe_id' => $recipe1->id]);
    $day1 = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 1]);
    MealAssignment::factory()->create(['meal_plan_day_id' => $day1->id, 'meal_plan_recipe_id' => $mpRecipe1Pivot->id, 'to_cook' => true]);

    // Recipe 2: Garlic without quantity (should be ignored)
    $recipe2 = Recipe::factory()->create(['user_id' => $user->id]);
    $recipe2->ingredients()->attach($ingredient, ['amount' => null, 'unit' => null]);
    $mpRecipe2Pivot = MealPlanRecipe::create(['meal_plan_id' => $mealPlan->id, 'recipe_id' => $recipe2->id]);
    $day2 = MealPlanDay::factory()->create(['meal_plan_id' => $mealPlan->id, 'day_number' => 2]);
    MealAssignment::factory()->create(['meal_plan_day_id' => $day2->id, 'meal_plan_recipe_id' => $mpRecipe2Pivot->id, 'to_cook' => true]);

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test', 'full');

    // Should only contain the Garlic item with quantity
    expect($shoppingList->items)->toHaveCount(1);
    $item = $shoppingList->items->first();
    expect($item->name)->toBe('Garlic')
        ->and((float) $item->quantity)->toBe(2.0)
        ->and($item->unit)->toBe('cloves');
});

// Helper function to create shopping list data for display tests
function setupShoppingListForDisplay(): array
{
    $user = User::factory()->create();
    $shoppingList = ShoppingList::factory()->create(['user_id' => $user->id]);

    // Items with categories and different orders
    $item1 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Milk', 'category' => 'Dairy', 'order' => 2]);
    $item2 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Cheese', 'category' => 'Dairy', 'order' => 1]);
    $item3 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Apples', 'category' => 'Produce', 'order' => 1]);
    $item4 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Bread', 'category' => null, 'order' => 1]); // Uncategorized

    return compact('shoppingList', 'item1', 'item2', 'item3', 'item4');
}

test('prepareForDisplay correctly groups items by category and sorts categories', function () {
    $data = setupShoppingListForDisplay();
    $shoppingList = $data['shoppingList'];

    $preparedData = $this->service->prepareForDisplay($shoppingList);

    expect($preparedData['use_categories'])->toBeTrue();
    $itemsByCategory = $preparedData['items_by_category'];

    // Check category order (alphabetical, Uncategorized last)
    expect(array_keys($itemsByCategory->all()))->toBe(['Dairy', 'Produce', 'Uncategorized']);

    // Check items within Dairy category (sorted by order)
    $dairyItems = $itemsByCategory['Dairy'];
    expect($dairyItems)->toHaveCount(2);
    expect($dairyItems[0]['name'])->toBe('Cheese'); // order 1
    expect($dairyItems[1]['name'])->toBe('Milk');   // order 2

    // Check items within Produce category
    $produceItems = $itemsByCategory['Produce'];
    expect($produceItems)->toHaveCount(1);
    expect($produceItems[0]['name'])->toBe('Apples');

    // Check items within Uncategorized category
    $uncategorizedItems = $itemsByCategory['Uncategorized'];
    expect($uncategorizedItems)->toHaveCount(1);
    expect($uncategorizedItems[0]['name'])->toBe('Bread');
});

test('prepareForDisplay handles lists with only uncategorized items', function () {
    $user = User::factory()->create();
    $shoppingList = ShoppingList::factory()->create(['user_id' => $user->id]);

    // All items are uncategorized
    $item1 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Water', 'category' => null, 'order' => 2]);
    $item2 = ShoppingListItem::factory()->create(['shopping_list_id' => $shoppingList->id, 'name' => 'Juice', 'category' => null, 'order' => 1]);

    $preparedData = $this->service->prepareForDisplay($shoppingList);

    expect($preparedData['use_categories'])->toBeFalse();
    expect($preparedData)->not->toHaveKey('items_by_category');

    // Items should still be loaded and sorted on the main list resource
    $items = $preparedData['items'];
    expect($items)->toHaveCount(2);
    expect($items[0]['name'])->toBe('Juice'); // order 1
    expect($items[1]['name'])->toBe('Water'); // order 2
});
