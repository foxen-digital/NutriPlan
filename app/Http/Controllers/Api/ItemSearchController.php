<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\ShoppingListItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemSearchController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->input('query');

        if (empty($query) || strlen((string) $query) < 2) {
            return response()->json([]);
        }

        // Search in ShoppingListItem names
        $shoppingItems = ShoppingListItem::where('name', 'like', "%{$query}%")
            ->distinct()
            ->limit(5)
            ->pluck('name');

        // Search in Ingredient names
        $ingredients = Ingredient::where('name', 'like', "%{$query}%")
            ->distinct()
            ->limit(5)
            ->pluck('name');

        // Combine and ensure uniqueness
        $results = $shoppingItems->merge($ingredients)
            ->unique()
            ->values()
            ->toArray();

        return response()->json($results);
    }
}
