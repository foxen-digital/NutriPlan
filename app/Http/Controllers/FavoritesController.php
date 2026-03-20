<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoritesController extends Controller
{
    /**
     * Display a listing of the user's favorite recipes.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        /** @var \Illuminate\Pagination\LengthAwarePaginator<Recipe> $favorites */
        $favorites = $user->favorites()
            ->with(['user:id,name,slug', 'categories' => function ($query): void {
                $query->withCount('recipes')
                    ->orderBy('recipes_count', 'desc');
            }])
            ->withCount('ingredients')
            ->paginate(12)
            ->withQueryString();

        // Add is_favorited flag to each recipe
        $favorites->getCollection()->each(function (Recipe $recipe): void {
            $recipe->is_favorited = true;
        });

        return Inertia::render('Recipes/Favorites', [
            'favorites' => $favorites,
        ]);
    }
}
