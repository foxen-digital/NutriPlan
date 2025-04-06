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
        $this->authorize('update', $shoppingList);

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
        $this->authorize('delete', $shoppingList);

        $shoppingList->delete();

        return redirect()->route('shopping-lists.index')
            ->with('success', 'Shopping list deleted successfully.');
    }
}
