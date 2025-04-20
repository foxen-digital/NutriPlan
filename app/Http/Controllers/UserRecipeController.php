<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RecipeIndexService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserRecipeController extends Controller
{
    /**
     * Display a listing of the recipes for a specific user.
     */
    public function index(Request $request, User $user, RecipeIndexService $recipeService): Response
    {
        $currentUser = $request->user();
        $isOwner = $currentUser->id === $user->id;

        // Prepare filters for the service
        $filters = $request->only(['category', 'search_term', 'search_mode']);
        $filters['target_user_id'] = $user->id; // Add the target user ID

        // Ensure search_term is null if empty string
        if (isset($filters['search_term']) && trim($filters['search_term']) === '') {
            $filters['search_term'] = null;
        }

        // Get recipes using the service
        $recipes = $recipeService->getRecipes($currentUser, $filters);

        // Append all query parameters
        $recipes->appends($request->query());

        return Inertia::render('Recipes/UserRecipes', [
            'recipes' => $recipes,
            'filter' => $filters, // Pass all filters including search and target_user_id
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => $user->slug,
            ],
            'isOwner' => $isOwner,
        ]);
    }
}
