<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\AddRecipeToCollectionAction;
use App\Http\Requests\StoreCollectionRecipeRequest;
use App\Models\Collection;
use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CollectionRecipeController extends Controller
{
    /**
     * Store a recipe in a collection.
     *
     * This method handles the association between a recipe and a collection,
     * allowing users to organize recipes into their collections.
     *
     * @param StoreCollectionRecipeRequest $request The HTTP request containing collection_id and recipe_id
     * @param AddRecipeToCollectionAction $action The action to handle the business logic
     */
    public function store(StoreCollectionRecipeRequest $request, AddRecipeToCollectionAction $action): RedirectResponse
    {
        $validatedData = $request->validated();

        $collection = Collection::query()->findOrFail($validatedData['collection_id']);
        $recipe = Recipe::query()->findOrFail($validatedData['recipe_id']);

        $action->handle($collection, $recipe);

        return back()->with('success', 'Recipe added to collection successfully.');
    }

    /**
     * Remove a recipe from a collection.
     *
     * This method handles the removal of a recipe from a collection,
     * maintaining the organization of recipes within collections.
     *
     * @param Collection $collection The collection to remove the recipe from
     * @param Recipe $recipe The recipe to be removed
     */
    public function destroy(Collection $collection, Recipe $recipe): RedirectResponse
    {
        Gate::authorize('update', $collection);

        $collection->recipes()->detach($recipe->id);

        return back()->with('success', 'Recipe removed from collection successfully.');
    }
}
