# Meal Planning - Phase 8: Limited Shopping List Synchronization (On New Meal Addition)

## Overview
This phase implements a limited automatic synchronization for generated shopping lists. Shopping lists will automatically update to include ingredients **only when a new meal assignment is created** within the list's date range and marked as "to cook".

This approach ensures that if a user adds a new meal they intend to cook to their plan after generating a list, the list reflects this addition. However, subsequent modifications (like changing servings, toggling `to_cook` off) or deletions of meal assignments **will not** alter the shopping list. This prevents ingredients from being removed if plans change and supports scenarios like planning leftovers.

## Depends On
- Phase 7a: Shopping List Generation
- Laravel Event System

## Leads To
- Phase 7c: Enhancements (Unit Conversion, Categorization)

## Core Functionality

### Limited Automatic Updates
- Shopping lists generated from a meal plan (Phase 7a) will automatically update **only** in response to the **creation** of a **new** `MealAssignment` record where:
    - The assignment's `date` falls within the shopping list's `start_date` and `end_date`.
    - The assignment's `to_cook` flag is set to `true`.
- **No updates** occur for:
    - Deleting any `MealAssignment`.
    - Updating an existing `MealAssignment` (changing `date`, `servings`, or `to_cook` status).

### Update Logic
- When a relevant `MealAssignmentCreated` event occurs, the system identifies any potentially affected `ShoppingList` (based on `start_date` and `end_date`).
- For each affected list, the ingredients from the newly assigned recipe (scaled by `servings`) are added:
    - If an ingredient already exists on the list (same `ingredient_id` and `unit`), its quantity is incremented.
    - If an ingredient does not exist, a new `ShoppingListItem` is created.
- Consolidation logic (basic, from Phase 7a) is applied during these additions.

## Implementation Details

### Database Schema
*(No changes needed from Phase 7a schema)*

### Models
*(No changes needed from Phase 7a schema)*

### Events (`App\Events\...`)
We only need an event specifically for creation.

```php
<?php
namespace App\Events;

use App\Models\MealAssignment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MealAssignmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public MealAssignment $mealAssignment // Contains meal_plan_id, recipe_id, date, servings, to_cook
    ) {}
}
```
*Note: This event should be dispatched from the controller/service responsible for creating `MealAssignment` records.* 

### Event Listeners (`App\Listeners\...`)
This listener reacts *only* to the creation event.

```php
<?php
namespace App\Listeners;

use App\Events\MealAssignmentCreated;
use App\Services\ShoppingListUpdateService; // Service with simplified logic
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateShoppingListOnMealCreation implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly ShoppingListUpdateService $shoppingListUpdateService
    ) {}

    public function handle(MealAssignmentCreated $event): void
    {
        // Check if the new assignment is marked 'to_cook'
        if ($event->mealAssignment->to_cook) {
            $this->shoppingListUpdateService->handleAssignmentCreation($event->mealAssignment);
        }
    }
}
```

### Service (`App\Services\ShoppingListUpdateService`)
This service now only needs logic to handle adding/incrementing items for newly created assignments.

```php
<?php
namespace App\Services;

use App\Models\MealAssignment;
use App\Models\ShoppingList;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
// ... other necessary imports (e.g., Repositories if used)

class ShoppingListUpdateService
{
    // Constructor potentially injecting IngredientRepository, RecipeRepository

    /**
     * Handles the creation of a new MealAssignment marked 'to_cook'.
     * Finds relevant shopping lists and adds/increments the required ingredients.
     */
    public function handleAssignmentCreation(MealAssignment $assignment): void
    {
        // Ensure it's marked to cook (double check, though listener might already do this)
        if (!$assignment->to_cook) {
            return;
        }

        $affectedLists = $this->findAffectedLists($assignment->date);
        if ($affectedLists->isEmpty()) {
            return;
        }

        $recipe = Recipe::with('ingredients')->find($assignment->recipe_id);
        if (!$recipe) {
            // Log error: Recipe not found for assignment
            return;
        }

        foreach ($affectedLists as $list) {
            $this->addOrIncrementIngredients($list, $recipe, $assignment->servings);
        }
    }

    /**
     * Finds ShoppingLists whose date range includes the given date.
     */
    private function findAffectedLists(string $date): Collection
    {
        // Find lists where date is between start_date and end_date
        return ShoppingList::where('start_date', '<=', $date)
                           ->where('end_date', '>=', $date)
                           ->get();
    }

    /**
     * Adds ingredients from a recipe to a shopping list, incrementing
     * quantities if the item already exists.
     */
    private function addOrIncrementIngredients(ShoppingList $list, Recipe $recipe, int $servings): void
    {
        // Logic similar to Phase 7a generation, but additive:
        // 1. Get recipe ingredients scaled by servings.
        // 2. For each scaled ingredient:
        //    a. Try to find a matching ShoppingListItem (ingredient_id, unit) on the $list.
        //    b. If found, update its quantity by adding the scaled ingredient amount.
        //    c. If not found, create a new ShoppingListItem with the scaled amount.
        //    d. Handle potential unit conversions/consolidation if necessary (as per 7a/7c)
    }

    // NOTE: Methods related to handling updates or deletions (removeOrDecrementIngredients, adjustIngredientQuantities, handleAssignmentChange, handleAssignmentDeletion, wasAffectingList, getOriginalServings etc.) are NO LONGER NEEDED in this service for Phase 7b.
}
```

### Event Service Provider (`App\Providers\EventServiceProvider`)
Register the specific listener for the creation event.
```php
protected $listen = [
    MealAssignmentCreated::class => [
        UpdateShoppingListOnMealCreation::class,
    ],
    // Ensure listeners for MealAssignmentChanged/Updated/Deleted are NOT registered for this purpose
];
```

### User Interface
- Users create shopping lists as per Phase 7a.
- If a user later adds a *new* meal to the plan within the list's date range and marks it `to_cook`, the ingredients will be automatically added to the list.
- A subtle notification or indicator that the list was updated might be helpful but is not core to this phase.
- Help text should clarify that only *newly added* 'to cook' meals update the list; modifications/deletions do not.

## Testing Strategy

### Unit Tests
- Test `ShoppingListUpdateService` methods:
    - `findAffectedLists` logic.
    - `addOrIncrementIngredients` logic:
        - Correctly calculates scaled ingredient quantities.
        - Correctly increments existing items.
        - Correctly adds new items.
        - Handles different units correctly (basic consolidation).
- Test Event/Listener registration (`MealAssignmentCreated` -> `UpdateShoppingListOnMealCreation`).
- Test the listener correctly filters for `to_cook = true` assignments.

### Integration Tests
- Test the full flow: Create a `MealAssignment` with `to_cook = true` -> `MealAssignmentCreated` event dispatched -> Listener triggers -> Service executes -> `ShoppingListItem` quantities/presence are correctly updated (added or incremented) in the database.
- Test scenarios:
    - Creating a new `to_cook` assignment within a list's range -> List updates.
    - Creating a new assignment with `to_cook = false` within a list's range -> List **does not** update.
    - Creating a new `to_cook` assignment *outside* a list's date range -> List **does not** update.
    - **Verify:** Updating an *existing* assignment (e.g., toggling `to_cook`, changing servings) -> List **does not** update.
    - **Verify:** Deleting an assignment -> List **does not** update.

## Future Considerations (Handled in subsequent phases)
- More robust unit conversion integrated into updates (Phase 7c).
- Categorization of shopping list items (Phase 7c).
- UI notifications indicating the list has been updated automatically.
- Performance considerations for large meal plans/many lists. 
