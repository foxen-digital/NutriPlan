<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeIndexService
{
    /**
     * Get filtered and paginated recipes.
     *
     * @param User $user The current user
     * @param array $filters Filters to apply (category, show_mine)
     * @return LengthAwarePaginator
     */
    public function getRecipes(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->buildBaseQuery();
        
        $this->applyFilters($query, $filters, $user);
        
        $recipes = $query->paginate(12);
        
        $this->addIsFavoritedFlag($recipes, $user);
        
        return $recipes;
    }
    
    /**
     * Build the base query for recipes.
     *
     * @return Builder
     */
    private function buildBaseQuery(): Builder
    {
        return Recipe::query()
            ->with(['user:id,name,slug', 'categories' => function (Builder|BelongsToMany $query): void {
                $query->withCount('recipes');
            }])
            ->latest();
    }
    
    /**
     * Apply filters to the query.
     *
     * @param Builder $query
     * @param array $filters
     * @param User $user
     * @return void
     */
    private function applyFilters(Builder $query, array $filters, User $user): void
    {
        // Apply search filter first
        if (isset($filters['search_term']) && $filters['search_term']) {
            $searchTerm = $filters['search_term'];
            $searchMode = $filters['search_mode'] ?? 'name_description';

            if ($searchMode === 'name_description') {
                $query->where(function (Builder $subQuery) use ($searchTerm): void {
                    $subQuery->where('title', 'LIKE', "%{$searchTerm}%")
                             ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                });
            } elseif ($searchMode === 'ingredient') {
                $query->whereHas('ingredients', function (Builder $subQuery) use ($searchTerm): void {
                    // Assuming the Ingredient model has a 'name' column
                    $subQuery->where('ingredients.name', 'LIKE', "%{$searchTerm}%");
                });
            }
        }

        // Filter by category
        if (isset($filters['category'])) {
            $query->whereHas('categories', function (Builder|BelongsToMany $query) use ($filters): void {
                $query->where('categories.id', $filters['category']);
            });
        }

        // Filter by user's own recipes
        if (isset($filters['show_mine']) && $filters['show_mine']) {
            $query->where('user_id', $user->id);
        } else {
            // Only show public recipes or user's own recipes
            $query->where(function (Builder $query) use ($user): void {
                $query->where('is_public', true)
                    ->orWhere('user_id', $user->id);
            });
        }
    }
    
    /**
     * Add is_favorited flag to recipes.
     *
     * @param LengthAwarePaginator $recipes
     * @param User $user
     * @return void
     */
    private function addIsFavoritedFlag(LengthAwarePaginator $recipes, User $user): void
    {
        $recipes->getCollection()->transform(function (Recipe $recipe) use ($user): Recipe {
            $recipe->is_favorited = $user->favorites()->where('recipe_id', $recipe->id)->exists();
            return $recipe;
        });
    }
} 