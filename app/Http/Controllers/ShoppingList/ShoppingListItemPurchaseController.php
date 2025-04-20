<?php

declare(strict_types=1);

namespace App\Http\Controllers\ShoppingList;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

class ShoppingListItemPurchaseController extends Controller
{
    use AuthorizesRequests;

    /**
     * Toggle the purchased status of an item
     * POST /shopping-lists/{shoppingList}/items/{item}/toggle-purchased
     */
    public function __invoke(ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
    {
        $this->authorize('update', $shoppingList);

        // Ensure the item belongs to the shopping list
        if ($item->shopping_list_id !== $shoppingList->id) {
            abort(404);
        }

        $item->update([
            'is_purchased' => !$item->is_purchased,
        ]);

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Item ' . ($item->is_purchased ? 'marked as purchased' : 'marked as not purchased') . '.');
    }
}
