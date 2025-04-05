# Meal Planning - Phase 7: Core Shopping List Management

## Overview
This phase establishes the foundational functionality for shopping lists. Users will be able to create empty lists, manually add, edit, and remove custom items, and track purchased items. This phase focuses on manual list management without integration into meal plan generation.

## Depends On
- User Authentication (Implicit)
- Basic Application Structure

## Leads To
- Phase 7a: Shopping List Generation

## Core Functionality

### Shopping List Management
- Create new, empty shopping lists.
- View a list of existing shopping lists.
- View the details and items of a specific shopping list.
- Edit the name and other basic details of a shopping list.
- Delete shopping lists.

### Custom Item Management
- Manually add custom items to a shopping list (name, quantity, unit, category - initially free text).
- Edit the details of custom items.
- Remove custom items from a shopping list.

### Purchase Tracking
- Mark individual items (custom only in this phase) as purchased/acquired.
- Unmark items as purchased.

## Implementation Details

### Database Schema

```sql
-- Shopping Lists
CREATE TABLE shopping_lists (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    -- start_date/end_date might be added later, not essential for core
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Shopping List Items
CREATE TABLE shopping_list_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    shopping_list_id BIGINT UNSIGNED NOT NULL,
    ingredient_id BIGINT UNSIGNED NULL, -- Null for custom items
    name VARCHAR(255) NOT NULL, -- Name of the item (could be custom or from ingredient)
    quantity DECIMAL(8,2) NULL,
    unit VARCHAR(50) NULL,
    category VARCHAR(100) NULL, -- Initially free text, enhancement in Phase 7c
    is_custom BOOLEAN NOT NULL DEFAULT TRUE, -- TRUE for items added manually in this phase
    is_purchased BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shopping_list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE SET NULL -- Link is optional for custom items
);
```
*Note: `ingredient_id` is included for forward compatibility but will be NULL for items created in this phase.*

### Models

#### ShoppingList Model (`App\Models\ShoppingList`)
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingList extends Model
{
    use HasFactory;

    // No $fillable, models are unguarded

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingListItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

#### ShoppingListItem Model (`App\Models\ShoppingListItem`)
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    use HasFactory;

    // No $fillable, models are unguarded

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_custom' => 'boolean',
        'is_purchased' => 'boolean',
    ];

    public function shoppingList(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class);
    }

    // Ingredient relationship is defined but optional for this phase
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
```

### Controllers

#### ShoppingListController (`App\Http\Controllers\ShoppingListController`)
Handles the core CRUD operations for shopping lists.

```php
<?php
// ... imports ...
class ShoppingListController extends Controller
{
    // Constructor injection for potential service later

    /**
     * Display a listing of the user's shopping lists
     * GET /shopping-lists
     */
    public function index(): Response // Using Inertia response
    {
        $shoppingLists = auth()->user()->shoppingLists;
        
        return Inertia::render('ShoppingLists/Index', [
            'shoppingLists' => ShoppingListResource::collection($shoppingLists),
        ]);
    }

    /**
     * Display the specified shopping list
     * GET /shopping-lists/{shoppingList}
     * Route Model Binding: Requires authorization policy
     */
    public function show(ShoppingList $shoppingList): Response // Using Inertia response
    {
        return Inertia::render('ShoppingLists/Show', [
            'shoppingList' => new ShoppingListResource($shoppingList->load('items')),
        ]);
    }

    /**
     * Store a new empty shopping list
     * POST /shopping-lists
     */
    public function store(StoreShoppingListRequest $request): RedirectResponse
    {
        $shoppingList = new ShoppingList($request->validated());
        $shoppingList->user_id = auth()->id();
        $shoppingList->save();

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Shopping list created successfully.');
    }

    /**
     * Update shopping list details (e.g., name)
     * PUT /shopping-lists/{shoppingList}
     */
    public function update(UpdateShoppingListRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        $shoppingList->update($request->validated());

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Shopping list updated successfully.');
    }

    /**
     * Delete the shopping list and its items (handled by DB cascade)
     * DELETE /shopping-lists/{shoppingList}
     */
    public function destroy(ShoppingList $shoppingList): RedirectResponse
    {
        $shoppingList->delete();

        return redirect()->route('shopping-lists.index')
            ->with('success', 'Shopping list deleted successfully.');
    }
}
```

#### ShoppingListItemController (`App\Http\Controllers\ShoppingListItemController`)
Handles CRUD operations for *custom* shopping list items in this phase.

```php
<?php
// ... imports ...
class ShoppingListItemController extends Controller
{
    // Constructor injection for potential service later

    /**
     * Add a custom item to the shopping list
     * POST /shopping-lists/{shoppingList}/items
     */
    public function store(StoreShoppingListItemRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        $item = new ShoppingListItem($request->validated());
        $item->shopping_list_id = $shoppingList->id;
        $item->is_custom = true;
        $item->save();

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item added successfully.');
    }

    /**
     * Update a custom shopping list item
     * PUT /shopping-lists/{shoppingList}/items/{item}
     * Route Model Binding: Ensure item belongs to list & user
     */
    public function update(UpdateShoppingListItemRequest $request, ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
    {
        $item->update($request->validated());

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove an item from the shopping list
     * DELETE /shopping-lists/{shoppingList}/items/{item}
     */
    public function destroy(ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item removed successfully.');
    }
}
```

#### ShoppingListItemPurchaseController (`App\Http\Controllers\ShoppingListItemPurchaseController`)
Handles the purchase status of shopping list items.

```php
<?php
// ... imports ...
class ShoppingListItemPurchaseController extends Controller
{
    // Constructor injection for potential service later

    /**
     * Toggle the purchased status of an item
     * POST /shopping-lists/{shoppingList}/items/{item}/toggle-purchased
     * Route Model Binding: Ensure item belongs to list & user
     */
    public function store(ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
    {
        $item->update([
            'is_purchased' => !$item->is_purchased,
        ]);

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item ' . ($item->is_purchased ? 'marked as purchased' : 'marked as not purchased') . '.');
    }

    // Bulk purchase update is deferred to Phase 7c
}
```

### Form Requests

#### StoreShoppingListRequest (`App\Http\Requests\StoreShoppingListRequest`)
```php
<?php
// ... imports ...
class StoreShoppingListRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
```

#### UpdateShoppingListRequest (`App\Http\Requests\UpdateShoppingListRequest`)
```php
<?php
// ... imports ...
class UpdateShoppingListRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}
```

#### StoreShoppingListItemRequest (`App\Http\Requests\StoreShoppingListItemRequest`)
*Note: Validates adding a **custom** item.*
```php
<?php
// ... imports ...
class StoreShoppingListItemRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            // is_custom defaults to true, no need to pass
        ];
    }
}
```

#### UpdateShoppingListItemRequest (`App\Http\Requests\UpdateShoppingListItemRequest`)
*Note: Validates updating a **custom** item.*
```php
<?php
// ... imports ...
class UpdateShoppingListItemRequest extends FormRequest
{
    public function authorize(): bool { return true; } // Add policy later

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
```

### User Interface

#### Views/Components
- Shopping Lists Index (`/shopping-lists`) - Displays list names, links to detail.
    - Should match Meal Plans Index (`resources/js/pages/MealPlan/Index.vue`) in style & layout
- Shopping List Detail (`/shopping-lists/{shoppingList}`) - Displays list name, items.
- Shopping List Creation Modal (Triggered from Index) - Simple version for empty lists.
- Shopping List Item Component (within Detail view) - Displays item details, edit/delete actions, purchase toggle.
- Add/Edit Custom Item Form (Modal or inline within Detail view).

#### Features
- Basic list display on index.
- Detailed view showing items.
- "Quick purchase toggle" button/checkbox per item.
- Buttons/icons for adding, editing, deleting custom items.
- Clear empty state on index page ("You have no shopping lists. Create one?").
- Empty state within a list ("This list has no items. Add one?").

#### Creation Workflows

**From Shopping List Index View (`/shopping-lists`):**
   - **Trigger:** "Create New Shopping List" button.
   - **UI:** Modal opens.
   - **Form Fields:**
     - `name`: Required text input.
   - **Action:** Submit triggers `ShoppingListController::store`.
   - **Feedback:** Modal closes, success notification, list appears in index.

## Testing Strategy

### Unit Tests
- Test model relationships (`ShoppingList` <-> `ShoppingListItem`, `User`).
- Test model attribute casting.

### Feature Tests
- Test `ShoppingListController` CRUD operations (create empty list, view index/show, update name, delete).
- Test `ShoppingListItemController` CRUD for *custom* items (add, update, delete).
- Test `ShoppingListItemPurchaseController` toggle endpoint.
- Test authorization (user can only see/manage their own lists/items).
- Test validation rules in Form Requests.

## Future Considerations (Handled in subsequent phases)
- Generating lists from meal plans.
- Automatic updates based on meal plan changes.
- Unit conversion.
- Ingredient categorization.
- Drag & drop reordering.
- Bulk actions. 