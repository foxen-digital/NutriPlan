# Meal Planning - Phase 7d: Shopping List UI Enhancements

## Overview
This phase introduces UI enhancements for the shopping list, focusing on reordering and filtering capabilities.

## Depends On
- Phase 7a: Shopping List Generation (for base shopping list structure)
- Phase 7b: Automatic Meal Plan Synchronization (optional, but ensures list reflects meal plan)

## Leads To
- Further UI refinements.

## Core Functionality

### UI Enhancements
- **Drag & Drop Reordering:** Allow users to manually reorder items within the shopping list view.
    - Requires storing an `order` column on `shopping_list_items`.
- **Filtering/Sorting:** Add UI controls to filter the list (e.g., show only purchased/unpurchased).

## Implementation Details

### Database Schema

```sql
-- Add order column to shopping_list_items
ALTER TABLE shopping_list_items
ADD COLUMN `order` INT UNSIGNED NULL DEFAULT 0 AFTER category; -- Or appropriate position
```

### Models

#### ShoppingListItem Model (`App\Models\ShoppingListItem`)
- Add `order` to `$fillable` or handle via direct assignment if unguarded.

### Controllers

#### ShoppingListItemOrderController (`App\\Http\\Controllers\\ShoppingList\\ShoppingListItemOrderController`)
*(New Controller)*
```php
<?php

namespace App\Http\Controllers;

use App\Models\ShoppingList;
use App\Http\Requests\UpdateItemOrderRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;


class ShoppingListItemOrderController extends Controller
{
    // Service injection (if needed, likely not for basic order update)

    /**
     * Update the order of items in a shopping list.
     * PUT /shopping-lists/{shoppingList}/items/order
     */
    public function __invoke(UpdateItemOrderRequest $request, ShoppingList $shoppingList): RedirectResponse
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

#### UpdateItemOrderRequest (`App\\Http\\Requests\\UpdateItemOrderRequest`)
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
    - Filtering controls.

## Testing Strategy

### Feature Tests
- Test `PUT /shopping-lists/{shoppingList}/items/order` endpoint.
- Test UI interactions:
    - Drag & drop persists order correctly.
    - Filtering updates the view.

## Future Considerations
- Persisting filter preferences. 
