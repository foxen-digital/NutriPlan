# Meal Planning - Phase 7c: Shopping List Enhancements

## Overview
This phase introduces several enhancements to improve the usability and functionality of the shopping list feature, including unit conversion, ingredient categorization, UI improvements like drag & drop, and list regeneration.

## Depends On
- Phase 7b: Automatic Meal Plan Synchronization

## Leads To
- Potential future phases (e.g., external app integration, pantry tracking).

## Core Functionality

### Unit Conversion
- Implement a system for converting between common cooking units (volume: ml, l, tsp, tbsp, cup, pt, qt, gal; weight: g, kg, oz, lb; potentially pieces/counts where applicable).
- Apply unit conversion during ingredient consolidation (Phase 7a generation and Phase 7b updates) to combine items even if their original recipe units differ (e.g., 1 cup flour + 100g flour might be consolidated to a single entry in grams or cups based on a preferred unit or density estimate).
- Allow users to potentially select preferred units for certain ingredients or globally (Advanced Feature).

### Ingredient Categorization
- Add a `category` field to the `ingredients` table (if not already present) or use a separate `categories` table with relationships.
- Populate ingredient categories (e.g., Produce, Dairy, Meat, Pantry, Spices).
- Update the `ShoppingListItem` to store/reflect this category.
- Allow users to group/sort the shopping list UI by category.
- Allow users to manually assign/change the category for custom items.

### UI Enhancements
- **Drag & Drop Reordering:** Allow users to manually reorder items within the shopping list view.
    - Requires storing an `order` column on `shopping_list_items`.
- **Bulk Purchase Update:** Implement the endpoint and UI for marking multiple selected items as purchased/unpurchased simultaneously.
- **Filtering/Sorting:** Add UI controls to filter the list (e.g., show only purchased/unpurchased) and sort (by name, category, order).
- **Progress Indicator:** Display a visual indicator (e.g., progress bar, percentage) showing how many items have been purchased.

### List Regeneration
- Implement the `ShoppingListGenerationController::update` method (`POST /shopping-lists/{shoppingList}/regenerate`).
- This action re-runs the generation logic (from Phase 7a) for the *existing* shopping list based on its stored `start_date` and `end_date` and the current state of the associated meal plan(s).
- **Important:** Decide on handling custom items and existing purchase statuses during regeneration:
    - Option 1: Replace *all* items, losing custom items and purchase status.
    - Option 2: Replace only non-custom items, preserving custom items and purchase statuses for all.
    - Option 3 (Complex): Try to merge, preserving custom items and matching purchase status where possible.
    - **Recommendation:** Start with Option 2 (replace non-custom, preserve custom/purchased status) as it's less destructive.

## Implementation Details

### Database Schema

```sql
-- Add order column to shopping_list_items
ALTER TABLE shopping_list_items
ADD COLUMN `order` INT UNSIGNED NULL DEFAULT 0 AFTER category; -- Or appropriate position

-- Add category_id to ingredients (Example - depends on Ingredient structure)
-- ALTER TABLE ingredients
-- ADD COLUMN category_id BIGINT UNSIGNED NULL AFTER unit;
-- ADD FOREIGN KEY (category_id) REFERENCES categories(id);

-- Or add category directly to shopping_list_items if not linking to ingredients.category
-- ALTER TABLE shopping_list_items MODIFY COLUMN category VARCHAR(100) ...;
```
*(Need to finalize category strategy - link to `ingredients` or store directly on `shopping_list_items`? Storing directly might be simpler if `ingredients.category` isn't reliable or user needs list-specific overrides)*

### Models

#### ShoppingListItem Model (`App\Models\ShoppingListItem`)
- Add `order` to `$fillable` or handle via direct assignment if unguarded.
- Potentially add relationship to `Category` if using a separate table and linking through `ingredients`.

### Services

#### ShoppingListService (`App\Services\ShoppingListService`)
- Enhance `consolidateIngredients` logic to incorporate unit conversion.
- Add method `regenerateList(ShoppingList $list)`.

#### UnitConversionService (`App\Services\UnitConversionService`)
*(New Service)*
- Methods like `convert(float $quantity, string $fromUnit, string $toUnit): ?float`.
- Maintain conversion factors/rules (potentially in config or DB table).
- Handle incompatible conversions (e.g., volume to weight without density).

### Controllers

#### ShoppingListGenerationController (`App\Http\Controllers\ShoppingListGenerationController`)
- Implement `update` method for regeneration.
```php
    /**
     * Regenerate/refresh an existing shopping list based on current meal plan state.
     * POST /shopping-lists/{shoppingList}/regenerate
     */
    public function update(ShoppingList $shoppingList): RedirectResponse
    {
        $regeneratedList = $this->shoppingListService->regenerateList($shoppingList);
        
        return redirect()->route('shopping-lists.show', $regeneratedList)
            ->with('success', 'Shopping list regenerated successfully.');
    }
```

#### ShoppingListItemPurchaseController (`App\Http\Controllers\ShoppingListItemPurchaseController`)
- Implement `bulkUpdate` method.
```php
    /**
     * Mark multiple items as purchased/unpurchased
     * POST /shopping-lists/{shoppingList}/items/bulk-purchase
     */
    public function bulkUpdate(BulkPurchaseRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        $validated = $request->validated();
        // Service logic to update purchase status for $validated['item_ids']
        
        // Example implementation:
        $shoppingList->items()
            ->whereIn('id', $validated['item_ids'])
            ->update(['is_purchased' => $validated['purchased']]);
            
        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Items ' . ($validated['purchased'] ? 'marked as purchased' : 'marked as not purchased') . '.');
    }
```

#### ShoppingListItemOrderController (`App\Http\Controllers\ShoppingListItemOrderController`)
*(New Controller)*
```php
<?php
// ... imports ...
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ShoppingListItemOrderController extends Controller
{
    // Service injection

    /**
     * Update the order of items in a shopping list.
     * PUT /shopping-lists/{shoppingList}/items/order
     */
    public function update(UpdateItemOrderRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        $itemIds = $request->validated()['item_ids'];
        
        // Update the order of each item
        foreach ($itemIds as $index => $itemId) {
            $shoppingList->items()->where('id', $itemId)->update(['order' => $index + 1]);
        }
        
        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Items reordered successfully.');
    }
}
```

### Form Requests

#### BulkPurchaseRequest (`App\Http\Requests\BulkPurchaseRequest`)
```php
<?php
// ... imports ...
class BulkPurchaseRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array'],
            // Ensure items belong to the specific shopping list in the route
            'item_ids.*' => ['required', 'integer', Rule::exists('shopping_list_items', 'id')->where('shopping_list_id', $this->route('shoppingList')->id)],
            'purchased' => ['required', 'boolean'],
        ];
    }
}
```

#### UpdateItemOrderRequest (`App\Http\Requests\UpdateItemOrderRequest`)
```php
<?php
// ... imports ...
class UpdateItemOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array'],
            // Validate that all provided IDs exist and belong to the current list
            'item_ids.*' => ['required', 'integer', Rule::exists('shopping_list_items', 'id')->where('shopping_list_id', $this->route('shoppingList')->id)],
            // Ensure all items of the list are present in the array? Optional, depends on frontend.
        ];
    }
}
```

### User Interface

#### Views/Components
- Shopping List Detail View enhancements:
    - Drag & Drop handles for items.
    - Checkboxes for bulk selection.
    - Category headers/grouping.
    - Sorting/Filtering controls.
    - Progress bar.
- Unit Conversion display options (e.g., show original unit vs converted unit?).
- Button/action to trigger list regeneration.

## Testing Strategy

### Unit Tests
- Test `UnitConversionService` thoroughly for various units and edge cases.
- Test `ShoppingListService::regenerateList` logic (especially handling of custom/purchased items based on chosen strategy).
- Test category assignment/retrieval.

### Feature Tests
- Test `POST /shopping-lists/{shoppingList}/regenerate` endpoint.
- Test `POST /shopping-lists/{shoppingList}/items/bulk-purchase` endpoint.
- Test `PUT /shopping-lists/{shoppingList}/items/order` endpoint.
- Test ingredient consolidation now correctly uses unit conversion.
- Test automatic updates (Phase 7b) now correctly use unit conversion.
- Test UI interactions:
    - Drag & drop persists order correctly.
    - Filtering/Sorting updates the view.
    - Bulk purchase works.
    - Category grouping is displayed correctly.

## Future Considerations
- More sophisticated unit conversion (e.g., density tables for volume<->weight).
- User-defined categories.
- User preferences for default units.
- Integration with external shopping apps. 