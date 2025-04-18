<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\ShoppingList;
use App\Services\ShoppingListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Resources\ShoppingListResource;
use App\Http\Requests\StoreShoppingListRequest;
use App\Http\Requests\UpdateShoppingListRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ShoppingListController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ShoppingListService $shoppingListService
    ) {
    }

    /**
     * Display a listing of the user's shopping lists
     * GET /shopping-lists
     */
    public function index(Request $request): Response
    {
        $shoppingLists = $request->user()->shoppingLists()->withCount('items')->get();

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
            'shoppingList' => $this->shoppingListService->prepareForDisplay($shoppingList),
        ]);
    }

    /**
     * Store a new empty shopping list
     * POST /shopping-lists
     */
    public function store(StoreShoppingListRequest $request): RedirectResponse
    {
        $shoppingList = $request->user()->shoppingLists()->create($request->validated());

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
