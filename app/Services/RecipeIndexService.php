<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeIndexService
{
    /**
     * Get filtered and paginated recipes.
     *
     * @param User $authenticatedUser The currently authenticated user viewing the recipes.
     * @param array $filters Filters to apply (category, show_mine, search_term, search_mode, target_user_id)
     */
    public function getRecipes(User $authenticatedUser, array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery();

        $this->applyFilters($query, $filters, $authenticatedUser);

        $recipes = $query->paginate(12);

        $this->addIsFavoritedFlag($recipes, $authenticatedUser);

        return $recipes;
    }

    /**
     * Build the base query for recipes.
     */
    private function buildBaseQuery(): Builder
    {
        return Recipe::query()
            ->with(['user:id,name,slug', 'categories' => function ($query): void {
                $query->withCount('recipes');
            }])
            ->latest();
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters(Builder $query, array $filters, User $authenticatedUser): void
    {
        // Apply search filter first
        if (!empty($filters['search_term'])) {
            $searchTerm = $filters['search_term'];
            $searchMode = $filters['search_mode'] ?? 'name_description';

            if ($searchMode === 'name_description') {
                $query->where(function (Builder $subQuery) use ($searchTerm): void {
                    $subQuery->where('title', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            } elseif ($searchMode === 'ingredient') {
                $query->whereHas('ingredients', function (Builder $subQuery) use ($searchTerm): void {
                    $subQuery->where('ingredients.name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }

        // Filter by category
        if (!empty($filters['category'])) {
            $query->whereHas('categories', function (Builder $query) use ($filters): void {
                $query->where('categories.id', $filters['category']);
            });
        }

        // Determine the target user ID and apply filters based on it and viewer permissions
        $targetUserId = $filters['target_user_id'] ?? null;

        if ($targetUserId) {
            // Viewing a specific user's profile
            $query->where('user_id', $targetUserId);
            // If the viewer is not the owner of the profile, only show public recipes
            if ($authenticatedUser->id !== (int)$targetUserId) {
                $query->where('is_public', true);
            }
        } elseif (!empty($filters['show_mine'])) {
            // Viewing "My Recipes" specifically (show_mine=true)
            $query->where('user_id', $authenticatedUser->id);
        } else {
            // Default view (index page without specific user or show_mine=true)
            // Show public recipes OR the authenticated user's own recipes
            $query->where(function (Builder $subQuery) use ($authenticatedUser): void {
                $subQuery->where('is_public', true)
                         ->orWhere('user_id', $authenticatedUser->id);
            });
        }
    }

    /**
     * Add is_favorited flag to recipes.
     */
    private function addIsFavoritedFlag(LengthAwarePaginator $recipes, User $authenticatedUser): void
    {
        $recipes->getCollection()->transform(function (Recipe $recipe) use ($authenticatedUser): Recipe {
            $recipe->is_favorited = $authenticatedUser->favorites()->where('recipe_id', $recipe->id)->exists();
            return $recipe;
        });
    }
}
