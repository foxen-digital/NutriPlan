<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreShoppingListItemRequest;
use App\Http\Requests\UpdateShoppingListItemRequest;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class ShoppingListItemController extends Controller
{
    use AuthorizesRequests;

    /**
     * Add a custom item to the shopping list
     * POST /shopping-lists/{shoppingList}/items
     */
    public function store(StoreShoppingListItemRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        $this->authorize('update', $shoppingList);

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
     */
    public function update(
        UpdateShoppingListItemRequest $request,
        ShoppingList $shoppingList,
        ShoppingListItem $item
    ): RedirectResponse {
        $this->authorize('update', $shoppingList);

        // Ensure the item belongs to the shopping list
        if ($item->shopping_list_id !== $shoppingList->id) {
            abort(404);
        }

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
        $this->authorize('update', $shoppingList);

        // Ensure the item belongs to the shopping list
        if ($item->shopping_list_id !== $shoppingList->id) {
            abort(404);
        }

        $item->delete();

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item removed successfully.');
    }
}
