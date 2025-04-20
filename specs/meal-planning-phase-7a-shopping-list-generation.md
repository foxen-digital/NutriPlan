# Meal Planning - Phase 7a: Shopping List Generation

## Overview
This phase builds upon the core shopping list functionality (Phase 7) by adding the ability to automatically generate shopping lists based on scheduled meals within a Meal Plan. It includes consolidating ingredients from multiple recipes.

## Depends On
- Phase 7: Core Shopping List Management
- Meal Planning Feature (Specifically Meal Plan, Recipe, Ingredient, and Meal Assignment models and data)

## Leads To
- Phase 7b: Automatic Meal Plan Synchronization

## Core Functionality

### Shopping List Generation
- Generate a shopping list from a specified Meal Plan for a given period (Full Plan, Week 1, Week 2).
- Automatically extract ingredient information (name, quantity, unit) from all recipes scheduled within the specified period and marked "to cook".
- Consolidate identical ingredients from different recipes, summing their quantities (basic consolidation, unit conversion deferred).
- Populate the shopping list with items derived from recipes (`is_custom=false`, `ingredient_id` linked).

### Ingredient Consolidation (Basic)
- Combine items with the same `ingredient_id`.
- Sum quantities *only if the units are identical*. (e.g., 2 cups flour + 1 cup flour = 3 cups flour).
- If units differ for the same ingredient (e.g., 1 cup flour + 50g flour), list them as separate items in this phase. Unit conversion is in Phase 7c.

## Implementation Details

### Database Schema
*(No changes needed from Phase 7 schema)*
- The `shopping_list_items` table will now store non-custom items (`is_custom = false`) with a populated `ingredient_id`.
- The `shopping_lists` table gets `start_date` and `end_date` fields to store the period the list was generated for.

```sql
-- Add columns to shopping_lists table (via migration)
ALTER TABLE shopping_lists
ADD COLUMN start_date DATE NULL AFTER name,
ADD COLUMN end_date DATE NULL AFTER start_date;
```

### Models

#### ShoppingList Model (`App\Models\ShoppingList`)
```php
// Add casts for new date fields
protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
];
```
*(No changes needed for `ShoppingListItem` model)*

### Services

#### ShoppingListService (`App\Services\ShoppingListService`)
*(Create or extend this service)*

```php
<?php
// ... imports ...
class ShoppingListService
{
    /**
     * Generate a Shopping List from a Meal Plan for a specific period.
     *
     * @param MealPlan $mealPlan The source meal plan.
     * @param string $name Name for the new list.
     * @param string $period "full", "week1", or "week2".
     * @return ShoppingList The newly generated shopping list.
     */
    public function generateFromMealPlan(MealPlan $mealPlan, string $name, string $period): ShoppingList
    {
        // 1. Determine start/end dates based on mealPlan start and period.
        // 2. Fetch relevant MealAssignments (within dates, to_cook=true).
        // 3. Eager load recipes and their ingredients.
        // 4. Extract all ingredients (ingredient_id, quantity, unit) from assignments/recipes.
        // 5. Consolidate ingredients (basic: group by ingredient_id and unit, sum quantities).
        // 6. Create ShoppingList record.
        // 7. Create ShoppingListItem records for each consolidated ingredient group.
        // 8. Return the created ShoppingList.
    }

    /**
     * Basic consolidation: Group by ingredient_id and unit, sum quantities.
     *
     * @param array $ingredients Array of ['ingredient_id' => id, 'quantity' => q, 'unit' => u]
     * @return array Consolidated array ['ingredient_id' => id, 'name' => name, 'quantity' => total_q, 'unit' => u]
     */
    private function consolidateIngredients(array $ingredients): array
    {
        // Implementation logic...
    }
}
```

### Controllers

#### ShoppingListGenerationController (`App\Http\Controllers\ShoppingList\ShoppingListGenerationController`)
Handles the generation requests.

```php
<?php
// ... imports ...
use App\Services\ShoppingListService;
use App\Models\MealPlan;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class ShoppingListGenerationController extends Controller
{
    public function __construct(
        private readonly ShoppingListService $shoppingListService
    ) {}

    /**
     * Generate a shopping list from a meal plan.
     * Automatically generates a default name, can be overridden.
     * POST /meal-plans/{mealPlan}/shopping-list
     * Route Model Binding: MealPlan
     */
    public function store(GenerateShoppingListRequest $request, MealPlan $mealPlan): RedirectResponse
    {
        $validated = $request->validated();

        // Determine default name if not provided
        $listName = $validated['name'] ?? 'Shopping List for ' . $mealPlan->name . ' - ' . ucfirst($validated['period']);

        $shoppingList = $this->shoppingListService->generateFromMealPlan(
            $mealPlan,
            $listName,
            $validated['period']
        );

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Shopping list generated successfully.');
    }

    // Regenerate functionality deferred to Phase 7c
    // public function update(ShoppingList $shoppingList): RedirectResponse
}
```

### Form Requests

#### GenerateShoppingListRequest (`App\Http\Requests\GenerateShoppingListRequest`)
```php
<?php
// ... imports ...
use Illuminate\Validation\Rule;

class GenerateShoppingListRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        // Determine allowed periods based on the route-bound MealPlan
        $mealPlan = $this->route('mealPlan');
        $allowedPeriods = ['full'];
        if ($mealPlan && $mealPlan->duration_days === 14) { // Assuming duration_days attribute exists
            $allowedPeriods = ['full', 'week1', 'week2'];
        }

        return [
            'name' => ['nullable', 'string', 'max:255'], // Optional, default will be generated
            'period' => ['required', 'string', Rule::in($allowedPeriods)],
            // 'include_uncategorized' => ['boolean'], // Defer category handling
        ];
    }
}
```

### User Interface

#### Views/Components
- Shopping List Creation Modal (Enhanced)

#### Features
- Generation workflow integrated into the creation modal.
- List Detail view now displays items generated from recipes (linked to ingredients) alongside custom items.

#### Creation Workflows (Enhancement)

**1. From Meal Plan View (`/meal-plans/{mealPlan}`):**
   - Modal includes `period` select based on plan duration.
   - `name` is pre-filled based on plan and default period, editable.
   - Submitting triggers generation via `ShoppingListGenerationController`.

**2. From Shopping List Index View (`/shopping-lists`):**
   - Modal includes "Generate from Meal Plan" option.
   - Requires selecting a `MealPlan`.
   - Dynamically shows `period` select based on chosen plan's duration.
   - `name` pre-filled, editable.
   - Submitting triggers generation via `ShoppingListGenerationController`.

## Testing Strategy

### Unit Tests
- Test `ShoppingListService::generateFromMealPlan` logic:
    - Correct date range calculation for periods.
    - Correct fetching of meal assignments.
    - Accurate extraction of ingredients.
    - Correct basic consolidation (summing quantities for same ingredient_id + unit).
- Test `GenerateShoppingListRequest` validation, especially dynamic `period` rules.

### Feature Tests
- Test `POST /meal-plans/{mealPlan}/shopping-list` endpoint:
    - Successful list generation with correct items and quantities (for same units).
    - Correct handling of different periods (full, week1, week2).
    - Validation errors for invalid requests.
    - Correct default name generation and override.
- Test that generated items have `is_custom=false` and correct `ingredient_id`.
- Test the UI workflow for generation from both entry points.

## Future Considerations (Handled in subsequent phases)
- Advanced unit conversion during consolidation.
- Automatic updates when meal plan changes.
- Ingredient categorization.
- List regeneration. 
