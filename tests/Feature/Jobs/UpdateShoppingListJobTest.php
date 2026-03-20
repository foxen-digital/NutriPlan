<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Events\ShoppingListUpdated;
use App\Jobs\UpdateShoppingListJob;
use App\Models\Ingredient;
use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\MealPlanRecipe;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use App\Services\UnitConversionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake(); // Prevent MealAssignmentObserver from executing jobs synchronously during setup
    Event::fake([ShoppingListUpdated::class]);
    $this->user = User::factory()->create();

    // Create shopping list for the user
    $this->shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Create meal plan with recipe and ingredients
    $this->mealPlan = MealPlan::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->recipe = Recipe::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Attach recipe to meal plan via pivot
    $this->mealPlan->recipes()->attach($this->recipe->id, ['scale_factor' => 1.0]);
    $this->mealPlanRecipe = MealPlanRecipe::where('meal_plan_id', $this->mealPlan->id)
        ->where('recipe_id', $this->recipe->id)
        ->first();

    // Create meal assignment - unsetRelations() clears any relations pre-loaded by MealAssignmentObserver
    // so that loadMissing() in UpdateShoppingListJob fetches fresh data from the database
    $this->mealAssignment = MealAssignment::factory()->create([
        'meal_plan_recipe_id' => $this->mealPlanRecipe->id,
    ]);
    $this->mealAssignment->unsetRelations();
});

it('increments quantity when ingredient exists with same unit', function () {
    // Arrange
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    // Add ingredient to recipe
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 50.0,
        'unit' => 'g',
    ]);

    // Create existing shopping list item with same ingredient and unit
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Flour',
        'quantity' => 100.0,
        'unit' => 'g',
        'is_custom' => false,
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    $existingItem->refresh();
    expect((float) $existingItem->quantity)->toBe(150.0) // 100 + 50
        ->and($this->shoppingList->items()->count())->toBe(1); // No new item created
});

it('creates new item when ingredient exists with different unit', function () {
    // Arrange
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    // Add ingredient to recipe with 'g' unit
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 50.0,
        'unit' => 'g',
    ]);

    // Create existing shopping list item with different unit (cups)
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Flour',
        'quantity' => 2.0,
        'unit' => 'cups',
        'is_custom' => false,
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    $existingItem->refresh();
    expect((float) $existingItem->quantity)->toBe(2.0) // Should NOT be incremented
        ->and($this->shoppingList->items()->count())->toBe(2); // New item created

    $newItem = $this->shoppingList->items()->where('unit', 'g')->first();
    expect((float) $newItem->quantity)->toBe(50.0)
        ->and($newItem->ingredient_id)->toBe($ingredient->id);
});

it('creates new shopping list item for new ingredient', function () {
    // Arrange
    $ingredient = Ingredient::factory()->create([
        'name' => 'Sugar',
    ]);

    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 25.0,
        'unit' => 'g',
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    $item = $this->shoppingList->items()->first();
    expect($item)->not->toBeNull()
        ->and($item->name)->toBe('Sugar')
        ->and((float) $item->quantity)->toBe(25.0)
        ->and($item->unit)->toBe('g')
        ->and($item->category)->toBeNull()
        ->and($item->ingredient_id)->toBe($ingredient->id)
        ->and($item->is_custom)->toBeFalse();
});

it('dispatches ShoppingListUpdated event on success', function () {
    // Arrange
    $ingredient = Ingredient::factory()->create(['name' => 'Salt']);
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 5.0,
        'unit' => 'g',
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    Event::assertDispatched(ShoppingListUpdated::class, function ($event) {
        return $event->userId === $this->user->id
            && $event->shoppingListId === $this->shoppingList->id
            && str_contains($event->message, $this->recipe->title);
    });
});

it('handles recipe with no ingredients gracefully', function () {
    // Arrange - Recipe with no ingredients

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert - No items created, event still dispatched
    expect($this->shoppingList->items()->count())->toBe(0);

    Event::assertDispatched(ShoppingListUpdated::class, function ($event) {
        return $event->shoppingListId === $this->shoppingList->id;
    });
});

it('handles multiple ingredients correctly', function () {
    // Arrange
    $flour = Ingredient::factory()->create(['name' => 'Flour']);
    $sugar = Ingredient::factory()->create(['name' => 'Sugar']);
    $eggs = Ingredient::factory()->create(['name' => 'Eggs']);

    $this->recipe->ingredients()->attach([
        $flour->id => ['amount' => 200.0, 'unit' => 'g'],
        $sugar->id => ['amount' => 100.0, 'unit' => 'g'],
        $eggs->id => ['amount' => 3.0, 'unit' => 'count'],
    ]);

    // Create existing item for flour
    ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $flour->id,
        'name' => 'Flour',
        'quantity' => 50.0,
        'unit' => 'g',
        'is_custom' => false,
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    expect($this->shoppingList->items()->count())->toBe(3);

    // Flour should be incremented
    $flourItem = $this->shoppingList->items()->where('ingredient_id', $flour->id)->first();
    expect((float) $flourItem->quantity)->toBe(250.0); // 50 + 200
});

it('sets order for new items at end of list', function () {
    // Arrange
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'order' => 1,
    ]);

    $ingredient = Ingredient::factory()->create(['name' => 'New Item']);
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 10.0,
        'unit' => 'g',
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert
    $newItem = $this->shoppingList->items()->where('ingredient_id', $ingredient->id)->first();
    expect($newItem->order)->toBe(2);
});

it('handles missing recipe relationship gracefully', function () {
    // Arrange - Delete the recipe to simulate broken relationship chain
    $this->recipe->delete();

    // Act & Assert - Should not throw exception
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);

    expect(fn () => $job->handle(new UnitConversionService()))->not->toThrow(Exception::class);

    // No items should be created and no event dispatched (nothing changed)
    expect($this->shoppingList->items()->count())->toBe(0);
    Event::assertNotDispatched(ShoppingListUpdated::class);
});

it('logs failure in failed method', function () {
    // Arrange
    Log::spy();
    $exception = new \Exception('Test failure');

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->failed($exception);

    // Assert
    Log::shouldHaveReceived('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'Shopping list update failed')
                && isset($context['shopping_list_id'])
                && isset($context['meal_assignment_id'])
                && isset($context['error']);
        });
});

it('skips ingredients with zero or null amount', function () {
    // Arrange
    $skippedIngredient = Ingredient::factory()->create(['name' => 'Zero Amount']);
    $validIngredient = Ingredient::factory()->create(['name' => 'Valid']);

    $this->recipe->ingredients()->attach($skippedIngredient->id, [
        'amount' => 0.0,
        'unit' => 'g',
    ]);
    $this->recipe->ingredients()->attach($validIngredient->id, [
        'amount' => 10.0,
        'unit' => 'g',
    ]);

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Assert - only valid ingredient creates an item
    expect($this->shoppingList->items()->count())->toBe(1);
    expect($this->shoppingList->items()->first()->name)->toBe('Valid');
});

it('eager loads relationships to prevent N+1 queries', function () {
    // Arrange - create ingredients without pre-loading relationships
    $ingredients = Ingredient::factory()->count(3)->create();
    foreach ($ingredients as $ingredient) {
        $this->recipe->ingredients()->attach($ingredient->id, [
            'amount' => 10.0,
            'unit' => 'g',
        ]);
    }

    // Use a fresh model to ensure no relations are pre-loaded (simulates queue deserialization)
    $freshMeal = MealAssignment::find($this->mealAssignment->id);
    expect($freshMeal->relationLoaded('mealPlanRecipe'))->toBeFalse();

    // Act
    $job = new UpdateShoppingListJob($this->shoppingList, $freshMeal);
    $job->handle(new UnitConversionService());

    // Assert - relationships are loaded after handle() via loadMissing
    expect($freshMeal->relationLoaded('mealPlanRecipe'))->toBeTrue();
    expect($this->shoppingList->items()->count())->toBe(3);
});

it('has correct retry configuration', function () {
    // Arrange & Act
    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);

    // Assert
    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe([5, 15, 30]);
});

it('converts incoming lb ingredient to g and increments matching existing item (metric user)', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Chicken Breast']);

    // Existing item: 300g
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Chicken Breast',
        'quantity' => 300.0,
        'unit' => MeasurementUnit::GRAM->value,
        'is_custom' => false,
    ]);

    // New recipe ingredient: 0.5 lb
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 0.5,
        'unit' => MeasurementUnit::POUND->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 0.5 lb = 226.796g → ceiling to nearest 5g = 230g → 300 + 230 = 530
    expect($this->shoppingList->items()->count())->toBe(1);
    expect((float) $existingItem->quantity)->toBe(530.0);
    expect($existingItem->unit)->toBe(MeasurementUnit::GRAM->value);
});

it('converts incoming ml ingredient to fl oz and increments matching existing item (imperial user)', function () {
    // Set user to imperial preference
    $this->user->addSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Imperial->value);

    $ingredient = Ingredient::factory()->create(['name' => 'Olive Oil']);

    // Existing item: 10 fl oz
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Olive Oil',
        'quantity' => 10.0,
        'unit' => MeasurementUnit::FLUID_OUNCE->value,
        'is_custom' => false,
    ]);

    // New recipe ingredient: 60ml
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 60.0,
        'unit' => MeasurementUnit::MILLILITER->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 60ml ÷ 29.5735 = ~2.03 fl oz → ceiling to nearest 5 fl oz = 5.0 fl oz → 10 + 5 = 15
    expect($this->shoppingList->items()->count())->toBe(1);
    expect((float) $existingItem->quantity)->toBe(15.0);
    expect($existingItem->unit)->toBe(MeasurementUnit::FLUID_OUNCE->value);
});

it('creates new item with preferred unit when ingredient not on list (metric user)', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Milk']);

    // New recipe ingredient: 2 cups (not on list yet)
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 2.0,
        'unit' => MeasurementUnit::CUP->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    expect($this->shoppingList->items()->count())->toBe(1);
    $newItem = $this->shoppingList->items()->first();
    // 2 cups = 480ml → already at 5ml boundary → 480ml
    expect($newItem->unit)->toBe(MeasurementUnit::MILLILITER->value);
    expect((float) $newItem->quantity)->toBe(480.0);
    expect($newItem->ingredient_id)->toBe($ingredient->id);
});

it('creates new separate item for cross-dimension ingredient without modifying existing (pass-through)', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    // Existing item: 200ml flour (volume)
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Flour',
        'quantity' => 200.0,
        'unit' => MeasurementUnit::MILLILITER->value,
        'is_custom' => false,
    ]);

    // New recipe ingredient: 100g flour (weight — cross-dimension to volume)
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 100.0,
        'unit' => MeasurementUnit::GRAM->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Existing item unchanged, new separate item created
    $existingItem->refresh();
    expect($this->shoppingList->items()->count())->toBe(2);
    expect((float) $existingItem->quantity)->toBe(200.0); // not modified
});

it('passes through dimensionless unit (piece) without conversion or rounding', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Eggs']);

    // Existing item: 3 pc
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Eggs',
        'quantity' => 3.0,
        'unit' => MeasurementUnit::PIECE->value,
        'is_custom' => false,
    ]);

    // New recipe ingredient: 2 pc
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 2.0,
        'unit' => MeasurementUnit::PIECE->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // Dimensionless: no conversion, no rounding — 3 + 2 = 5
    expect($this->shoppingList->items()->count())->toBe(1);
    expect((float) $existingItem->quantity)->toBe(5.0);
    expect($existingItem->unit)->toBe(MeasurementUnit::PIECE->value);
});

it('passes through ingredient with null unit unchanged', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Bay Leaves']);

    // New recipe ingredient: 3 with null unit
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 3.0,
        'unit' => null,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    expect($this->shoppingList->items()->count())->toBe(1);
    $newItem = $this->shoppingList->items()->first();
    expect((float) $newItem->quantity)->toBe(3.0);
    expect($newItem->unit)->toBeNull();
    expect($newItem->ingredient_id)->toBe($ingredient->id);
});

it('uses shopping list owner preference, not session user preference (FR8a)', function () {
    // Shopping list owner (User A) has imperial preference
    $this->user->addSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Imperial->value);

    // A second user (User B) exists but is irrelevant
    $otherUser = User::factory()->create(); // metric by default

    $ingredient = Ingredient::factory()->create(['name' => 'Butter']);

    // Existing item: 5 oz (imperial, because list owner is imperial)
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $ingredient->id,
        'name' => 'Butter',
        'quantity' => 5.0,
        'unit' => MeasurementUnit::OUNCE->value,
        'is_custom' => false,
    ]);

    // New recipe ingredient: 100g
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 100.0,
        'unit' => MeasurementUnit::GRAM->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 100g → oz (imperial preference from list owner) → ~3.53 oz → ceiling 0.1 oz = 3.6 oz → 5 + 3.6 = 8.6
    expect($this->shoppingList->items()->count())->toBe(1);
    expect($existingItem->unit)->toBe(MeasurementUnit::OUNCE->value);
    // 100g → 3.5274 oz → ceiling 0.1 oz = 3.6 oz → 5 + 3.6 = 8.6
    expect((float) $existingItem->quantity)->toBe(8.6);
});
