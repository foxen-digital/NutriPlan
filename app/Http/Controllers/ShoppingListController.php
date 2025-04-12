<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreShoppingListRequest;
use App\Http\Requests\UpdateShoppingListRequest;
use App\Http\Resources\ShoppingListResource;
use App\Models\ShoppingList;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShoppingListController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's shopping lists
     * GET /shopping-lists
     */
    public function index(): Response
    {
        $shoppingLists = auth()->user()->shoppingLists()->withCount('items')->get();

        return Inertia::render('ShoppingLists/Index', [
            'shoppingLists' => ShoppingListResource::collection($shoppingLists)->toArray(request()),
        ]);
    }

    /**
     * Display the specified shopping list
     * GET /shopping-lists/{shoppingList}
     */
    public function show(ShoppingList $shoppingList): Response
    {
        $this->authorize('view', $shoppingList);

        $shoppingList->load('items');
        
        // Group items by category
        $itemsByCategory = $shoppingList->items->groupBy(function ($item) {
            return $item->category ?? 'Uncategorized';
        });
        
        // Check if all items are uncategorized
        $allUncategorized = $itemsByCategory->keys()->count() === 1 && $itemsByCategory->has('Uncategorized');
        
        // Prepare shopping list data
        $shoppingListData = (new ShoppingListResource($shoppingList))->toArray(request());
        
        if ($allUncategorized) {
            // If all items are uncategorized, don't use categories
            $shoppingListData['use_categories'] = false;
        } else {
            // Use categories but ensure Uncategorized is always last
            $shoppingListData['use_categories'] = true;
            
            // Sort categories alphabetically
            $sorted = $itemsByCategory->sortKeys();
            
            // If 'Uncategorized' exists, move it to the end
            if ($sorted->has('Uncategorized')) {
                $uncategorized = $sorted->pull('Uncategorized');
                $sorted->put('Uncategorized', $uncategorized);
            }
            
            $shoppingListData['items_by_category'] = $sorted;
        }
        
        return Inertia::render('ShoppingLists/Show', [
            'shoppingList' => $shoppingListData,
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
        $this->authorize('update', $shoppingList);

        $shoppingList->update($request->validated());

        return redirect()->route('shopping-lists.index', $shoppingList)
            ->with('success', 'Shopping list updated successfully.');
    }

    /**
     * Delete the shopping list and its items (handled by DB cascade)
     * DELETE /shopping-lists/{shoppingList}
     */
    public function destroy(ShoppingList $shoppingList): RedirectResponse
    {
        $this->authorize('delete', $shoppingList);

        $shoppingList->delete();

        return redirect()->route('shopping-lists.index')
            ->with('success', 'Shopping list deleted successfully.');
    }
}
