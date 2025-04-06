<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GenerateShoppingListRequest;
use App\Models\MealPlan;
use App\Services\ShoppingListService;
use Illuminate\Http\RedirectResponse;

class ShoppingListGenerationController extends Controller
{
    public function __construct(
        private readonly ShoppingListService $shoppingListService
    ) {
    }

    /**
     * Generate a shopping list from a meal plan.
     * Automatically generates a default name, can be overridden.
     * POST /meal-plans/{mealPlan}/shopping-list
     */
    public function store(GenerateShoppingListRequest $request, MealPlan $mealPlan): RedirectResponse
    {
        $validated = $request->validated();

        // Determine default name if not provided
        $listName = $validated['name'] ?? 'Shopping List for ' . $mealPlan->name . ' - ' . ucfirst((string) $validated['period']);

        $shoppingList = $this->shoppingListService->generateFromMealPlan(
            $mealPlan,
            $listName,
            $validated['period']
        );

        return redirect()->route('shopping-lists.show', $shoppingList)
            ->with('success', 'Shopping list generated successfully.');
    }
}
