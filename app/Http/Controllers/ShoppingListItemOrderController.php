<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateItemOrderRequest;
use App\Models\ShoppingList;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ShoppingListItemOrderController extends Controller
{
    /**
     * Update the order of items in a shopping list.
     *
     * PUT /shopping-lists/{shoppingList}/order-items
     */
    public function __invoke(UpdateItemOrderRequest $request, ShoppingList $shoppingList): RedirectResponse
    {
        // Check if user owns this shopping list
        if ($shoppingList->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $itemIds = $request->validated()['item_ids'];

        // Update the order of each item
        foreach ($itemIds as $index => $itemId) {
            $shoppingList->items()->where('id', $itemId)->update(['order' => $index + 1]);
        }

        return redirect()->route('shopping-lists.show', ['shopping_list' => $shoppingList])
            ->with('success', 'Items reordered successfully.');
    }
}
