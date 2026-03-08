# Story 1.4: Background List Update Job

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a system,
I want a background job that updates shopping lists with new meal ingredients,
So that synchronization happens asynchronously without blocking user interactions.

## Acceptance Criteria

1. **Given** a shopping list and a new meal assignment, **When** the UpdateShoppingListJob processes, **Then** the job loads the recipe and ingredients for the meal **And** each ingredient is checked against existing list items

2. **Given** an ingredient already exists on the shopping list with the same unit, **When** the job processes the ingredient, **Then** the existing item's quantity is incremented by the meal's required amount **And** the item's updated_at timestamp is refreshed

3. **Given** an ingredient does not exist on the shopping list, **When** the job processes the ingredient, **Then** a new ShoppingListItem is created with the ingredient details **And** the item is ordered at the end of the list

4. **Given** the job completes successfully, **When** processing finishes, **Then** a ShoppingListUpdated event is dispatched with user ID and message **And** the event includes the shopping list ID

5. **Given** the job encounters a recoverable error (database timeout, deadlock), **When** the exception is caught, **Then** the job is retried with exponential backoff (5s, 15s, 30s) **And** the error is logged with context

6. **Given** the job fails after 3 retry attempts, **When** the failed method is called, **Then** the failure is logged with shopping list ID, meal ID, and error message **And** the job record is stored in failed_jobs table

7. **Given** the job processes ingredients, **When** relationships are loaded, **Then** the meal assignment's mealPlanRecipe and recipe relationships are eager loaded **And** ingredients are loaded with the recipe to prevent N+1 queries

## Tasks / Subtasks

- [x] Implement UpdateShoppingListJob handle() method (AC: 1, 2, 3, 7)
  - [x] Add retry configuration: `$tries = 3`, `$backoff = [5, 15, 30]`
  - [x] Eager load relationships: `mealPlanRecipe.recipe.ingredients`
  - [x] Get ingredients from recipe via relationship chain
  - [x] For each ingredient:
    - [x] Check if ShoppingListItem exists with matching ingredient_id and unit
    - [x] If exists: increment quantity, touch updated_at
    - [x] If not exists: create new ShoppingListItem
  - [x] Handle null safety on relationship chain

- [x] Implement ShoppingListUpdated event (AC: 4)
  - [x] Create event class implementing ShouldBroadcast
  - [x] Add constructor with userId, message, shoppingListId (public readonly)
  - [x] Implement broadcastOn() returning PrivateChannel
  - [x] Implement broadcastAs() returning 'shopping.list.updated'
  - [x] Implement broadcastWith() returning message and shoppingListId

- [x] Dispatch event from job (AC: 4)
  - [x] After successful processing, dispatch ShoppingListUpdated
  - [x] Include recipe title in message

- [x] Implement failed() method (AC: 5, 6)
  - [x] Log failure with shopping_list_id, meal_assignment_id, error message
  - [x] Use Log::error with structured context

- [x] Write feature tests for UpdateShoppingListJob
  - [x] Test existing ingredient with same unit gets quantity incremented
  - [x] Test existing ingredient with different unit creates new item
  - [x] Test new ingredient creates ShoppingListItem
  - [x] Test ShoppingListUpdated event is dispatched on success
  - [x] Test failed() method logs correctly
  - [x] Test eager loading prevents N+1 queries

- [x] Run `composer test:php` to ensure no regressions

## Dev Notes

### Architecture Context

This job is the **workhorse** of the synchronization system. It receives a shopping list and meal assignment, then updates the list with ingredients from the meal's recipe. This is where the addition-only constraint is enforced - we only ever add or increment, never remove.

**Event Flow Position:**
```
MealAssignment::created
         ↓
MealAssignmentObserver@created
         ↓
ShoppingListSyncService@syncNewMeal
         ↓
UpdateShoppingListJob::dispatch ← THIS STORY (one per list)
         ↓
Queue Worker → Update ingredients
         ↓
ShoppingListUpdated::dispatch → Toast notification
```

**Critical Design Decisions:**
- **Addition-Only:** Never remove ingredients - only add new or increment existing
- **Unit Matching:** Only increment if unit matches; different unit = new item
- **Parallel Processing:** Each list gets its own job for isolation
- **Failure Isolation:** One list failing doesn't affect others

### Previous Story Intelligence

**From Story 1.1 (Database Schema Addition):**
- `meal_plan_id` column exists in `shopping_lists` table
- `ShoppingList::mealPlan()` relationship is available

**From Story 1.2 (Meal Creation Observer):**
- Observer pre-loads relationships: `$meal->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients')`
- Service method receives MealAssignment with relationships loaded

**From Story 1.3 (Shopping List Sync Service):**
- UpdateShoppingListJob stub already exists with constructor:
  ```php
  public function __construct(
      public readonly ShoppingList $shoppingList,
      public readonly MealAssignment $meal
  ) {}
  ```
- Tests moved to Feature folder (database integration tests)
- Code review fixes added:
  - Null safety guard on relationship chain
  - user_id security filter on queries
  - Log::info on empty list case
- Patterns: `declare(strict_types=1);`, type hints, Pest `test()` function

### Relevant Architecture Patterns and Constraints

**Job Structure (architecture.md lines 244-256):**
```php
class UpdateShoppingListJob implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [5, 15, 30]; // exponential backoff

    public function __construct(
        public readonly ShoppingList $shoppingList,
        public readonly MealAssignment $meal
    ) {}

    public function handle(): void
    {
        // 1. Get ingredients from new meal
        // 2. For each ingredient:
        //    - If exists on list: increment quantity
        //    - If not exists: create new list item
    }
}
```

**Event Pattern (from RecipeImportCompleted.php):**
```php
class ShoppingListUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly int $userId,
        public readonly string $message,
        public readonly int $shoppingListId
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'shopping.list.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'shoppingListId' => $this->shoppingListId,
        ];
    }
}
```

**Logging Pattern (architecture.md lines 498-506):**
```php
Log::info('Shopping list sync initiated', [
    'meal_assignment_id' => $meal->id,
    'meal_plan_id' => $mealPlanId,
    'affected_lists' => $shoppingLists->count(),
]);

Log::error('Shopping list sync failed', [
    'shopping_list_id' => $list->id,
    'error' => $exception->getMessage(),
]);
```

### Source Tree Components to Touch

**Files to MODIFY:**
1. `app/Jobs/UpdateShoppingListJob.php` - Implement handle() and failed() methods

**Files to CREATE:**
1. `app/Events/ShoppingListUpdated.php` - Broadcast event for toast notifications
2. `tests/Feature/Jobs/UpdateShoppingListJobTest.php` - Job tests

### Model Relationships Reference

**Relationship Chain for Ingredients:**
```
MealAssignment
    → mealPlanRecipe (BelongsTo: MealPlanRecipe)
        → recipe (BelongsTo: Recipe)
            → ingredients (BelongsToMany: Ingredient)
                → pivot: amount, unit, description
```

**ShoppingListItem Structure:**
```php
// fillable: name, quantity, unit, category, is_custom, is_purchased, order
// casts: quantity => decimal:2, is_custom => boolean, is_purchased => boolean, order => integer
// relationships: shoppingList(), ingredient()
```

**Matching Logic:**
```php
// Find existing item by ingredient_id AND unit
$existingItem = $shoppingList->items()
    ->where('ingredient_id', $ingredient->id)
    ->where('unit', $pivot->unit)
    ->first();

if ($existingItem) {
    // Increment quantity
    $existingItem->increment('quantity', $pivot->amount);
    $existingItem->touch(); // Update timestamp
} else {
    // Create new item
    $shoppingList->items()->create([
        'name' => $ingredient->name,
        'quantity' => $pivot->amount,
        'unit' => $pivot->unit,
        'category' => $ingredient->category ?? null,
        'ingredient_id' => $ingredient->id,
        'is_custom' => false,
        'order' => $shoppingList->items()->max('order') + 1,
    ]);
}
```

### Testing Standards Summary

**Pest PHP Testing (from project-context.md):**
- Use `test()` function, NOT `@test` annotation
- Use factories for data creation
- Feature tests for database integration (tests/Feature/)
- Run `composer test:php` before marking complete

**Required Test Coverage:**
- Existing ingredient with same unit gets quantity incremented
- Existing ingredient with different unit creates new item (not increment)
- New ingredient creates ShoppingListItem with correct attributes
- ShoppingListUpdated event is dispatched on success
- Event broadcast to correct private channel
- failed() method logs error with context
- Eager loading prevents N+1 queries

**Testing Pattern:**
```php
test('existing ingredient with same unit is incremented', function () {
    // Arrange: Create shopping list with existing item, meal with same ingredient/unit
    $shoppingList = ShoppingList::factory()->create();
    $ingredient = Ingredient::factory()->create();
    $existingItem = ShoppingListItem::factory()
        ->for($shoppingList)
        ->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 2,
            'unit' => 'g',
        ]);

    $recipe = Recipe::factory()->hasAttached($ingredient, [
        'amount' => 50,
        'unit' => 'g',
    ])->create();
    $mealPlanRecipe = MealPlanRecipe::factory()->create(['recipe_id' => $recipe->id]);
    $meal = MealAssignment::factory()->create(['meal_plan_recipe_id' => $mealPlanRecipe->id]);

    // Act
    (new UpdateShoppingListJob($shoppingList, $meal))->handle();

    // Assert
    expect($existingItem->refresh()->quantity)->toBe(52.0); // 2 + 50
    expect($shoppingList->items()->count())->toBe(1); // No new item created
});

test('ShoppingListUpdated event is dispatched on success', function () {
    Event::fake(ShoppingListUpdated::class);

    // ... setup ...

    (new UpdateShoppingListJob($shoppingList, $meal))->handle();

    Event::assertDispatched(ShoppingListUpdated::class, function ($event) use ($shoppingList) {
        return $event->shoppingListId === $shoppingList->id
            && $event->userId === $shoppingList->user_id;
    });
});
```

### Project Structure Notes

**Alignment with unified project structure:**
- Job follows existing pattern in `app/Jobs/` (like `ImportRecipeJob.php`)
- Event follows existing pattern in `app/Events/` (like `RecipeImportCompleted.php`)
- Tests go in `tests/Feature/Jobs/`

**Detected conflicts or variances:** None identified - follows established patterns.

### Dependencies

**Requires Story 1.1 (COMPLETE):**
- ✅ ShoppingList model has `mealPlan()` relationship
- ✅ meal_plan_id column exists in shopping_lists table

**Requires Story 1.2 (COMPLETE):**
- ✅ MealAssignmentObserver exists and invokes ShoppingListSyncService

**Requires Story 1.3 (COMPLETE):**
- ✅ ShoppingListSyncService dispatches UpdateShoppingListJob
- ✅ UpdateShoppingListJob stub exists with constructor

**Blocks Story 1.5 (Synchronization Testing):**
- This story implements the job logic that tests will verify

### Critical Implementation Notes

**Eager Loading (AC: 7):**
The meal assignment comes with pre-loaded relationships from the observer, but we should ensure they're loaded in handle() as a safety net:
```php
public function handle(): void
{
    // Ensure relationships are loaded (defensive programming)
    $this->meal->loadMissing('mealPlanRecipe.recipe.ingredients');
    // ...
}
```

**Unit Matching Logic:**
Only increment if BOTH ingredient_id AND unit match:
- Same ingredient, same unit = increment quantity
- Same ingredient, different unit = create new item (e.g., 100g flour + 1 cup flour = 2 items)
- This prevents unit conversion issues

**Addition-Only Enforcement:**
The job must NEVER:
- Delete items from the list
- Decrement quantities
- Update existing items except to increment quantity

**Message Format for Event:**
```php
$message = "List updated with new ingredients from {$recipe->title}";
```

**Order for New Items:**
```php
$order = $shoppingList->items()->max('order') + 1;
```

### Edge Cases to Handle

1. **Recipe has no ingredients:** Job completes successfully, dispatches event with empty update message
2. **ShoppingList has no existing items:** All ingredients create new items
3. **Ingredient amount is null/zero:** Skip or use 0 (defensive)
4. **Unit is null on pivot:** Handle gracefully (treat as unmatched)
5. **Relationship chain is broken:** Null safety checks at each level

### References

- **Job Structure Architecture** [Source: _bmad-output/planning-artifacts/architecture.md#Background Processing lines 244-256]
- **Event Pattern** [Source: app/Events/RecipeImportCompleted.php]
- **Error Handling Pattern** [Source: _bmad-output/planning-artifacts/architecture.md#Error Handling Strategy lines 263-288]
- **Logging Pattern** [Source: _bmad-output/planning-artifacts/architecture.md#Logging Pattern lines 498-506]
- **Epic 1 Story 1.4 Requirements** [Source: _bmad-output/planning-artifacts/epics.md#Story 1.4 lines 222-264]
- **Previous Story 1.3** [Source: _bmad-output/implementation-artifacts/1-3-shopping-list-sync-service.md]
- **Existing Job Stub** [Source: app/Jobs/UpdateShoppingListJob.php]
- **ShoppingListItem Model** [Source: app/Models/ShoppingListItem.php]
- **Recipe Model (ingredients relationship)** [Source: app/Models/Recipe.php lines 61-66]
- **Project Context - Testing** [Source: _bmad-output/project-context.md#Testing Rules lines 107-135]

## Dev Agent Record

### Agent Model Used

claude-sonnet-4-6

### Debug Log References

### Completion Notes List

### File List

- app/Jobs/UpdateShoppingListJob.php (modified)
- app/Events/ShoppingListUpdated.php (created)
- app/Models/ShoppingListItem.php (modified)
- tests/Feature/Jobs/UpdateShoppingListJobTest.php (created)
