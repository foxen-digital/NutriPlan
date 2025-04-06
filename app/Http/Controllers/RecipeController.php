<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Recipe;
use App\Models\MealPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Actions\DeleteRecipeAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Recipe\CreateRecipeRequest;
use App\Http\Requests\Recipe\UpdateRecipeRequest;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecipeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Recipe::query()
            ->with(['user:id,name,slug', 'categories' => function (Builder|BelongsToMany $query): void {
                $query->withCount('recipes');
            }])
            ->latest();

        // Filter by category if provided in the request
        if ($request->has('category')) {
            $categoryId = $request->input('category');
            $query->whereHas('categories', function (Builder|BelongsToMany $query) use ($categoryId): void {
                $query->where('categories.id', $categoryId);
            });
        }

        // Filter by user's own recipes if show_mine is true
        if ($request->boolean('show_mine')) {
            $query->where('user_id', $user->id);
        } else {
            // Only show public recipes or user's own recipes
            $query->where(function (Builder $query) use ($user): void {
                $query->where('is_public', true)
                    ->orWhere('user_id', $user->id);
            });
        }

        $recipes = $query->paginate(12);

        // Add is_favorited flag to each recipe
        $recipes->getCollection()->transform(function (Recipe $recipe) use ($user): Recipe {
            $recipe->is_favorited = $user->favorites()->where('recipe_id', $recipe->id)->exists();
            return $recipe;
        });

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'filter' => $request->only(['category', 'show_mine']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Recipes/Create', [
            'categories' => \App\Models\Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'ingredients' => \App\Models\Ingredient::query()->orderBy('name')->get(['id', 'name']),
            'measurementUnits' => config('recipe.measurement_units'),
        ]);
    }

    public function store(CreateRecipeRequest $request): RedirectResponse
    {
        $recipe = $request->user()->recipes()->create($request->only([
            'title',
            'description',
            'instructions',
            'prep_time',
            'cooking_time',
            'servings',
            'is_public',
        ]));

        if ($request->has('categories')) {
            $recipe->categories()->sync($request->input('categories'));
        }

        if ($request->has('ingredients')) {
            $recipe->ingredients()->sync(
                collect($request->input('ingredients'))
                    ->mapWithKeys(fn (array $ingredient): array => [
                        $ingredient['ingredient_id'] => [
                            'amount' => $ingredient['amount'],
                            'unit' => $ingredient['unit'],
                        ],
                    ])
                    ->toArray()
            );
        }

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe created successfully.');
    }

    public function show(Recipe $recipe): Response
    {
        $this->authorize('view', $recipe);

        $user = request()->user();
        $recipe->load([
            'user:id,name,slug',
            'categories' => function (Builder|BelongsToMany $query): void {
                $query->select(['categories.id', 'categories.name', 'categories.slug']);
            },
            'nutritionInformation',
            'ingredients'
        ]);

        // Add is_favorited flag to the recipe
        $recipe->is_favorited = $user->favorites()->where('recipe_id', $recipe->id)->exists();

        // Handle imported recipe special visibility
        $isOwner = $user->id === $recipe->user_id;
        $hideDetails = !$isOwner && $recipe->isImported() && $recipe->is_public;

        return Inertia::render('Recipes/Show', [
            'recipe' => $recipe,
            'isOwner' => $isOwner,
            'hideDetails' => $hideDetails,
            'mealPlans' => $user->mealPlans()
                ->where('start_date', '>=', Carbon::now()->format('Y-m-d'))
                ->select(['id', 'name', 'start_date', 'duration'])
                ->get()
                ->filter(fn (MealPlan $mealPlan): bool => $mealPlan->start_date->addDays($mealPlan->duration) >= Carbon::now()->format('Y-m-d')),
        ]);
    }

    public function edit(Recipe $recipe): Response
    {
        $this->authorize('update', $recipe);

        $recipe->load([
            'categories' => function (Builder|BelongsToMany $query): void {
                $query->select(['categories.id', 'categories.name', 'categories.slug']);
            },
            'nutritionInformation',
            'ingredients'
        ]);

        return Inertia::render('Recipes/Edit', [
            'recipe' => $recipe,
            'categories' => \App\Models\Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'ingredients' => \App\Models\Ingredient::query()->orderBy('name')->get(['id', 'name']),
            'measurementUnits' => config('recipe.measurement_units'),
        ]);
    }

    public function update(UpdateRecipeRequest $request, Recipe $recipe): RedirectResponse
    {
        $this->authorize('update', $recipe);

        $recipe->update($request->safe([
            'title',
            'description',
            'instructions',
            'prep_time',
            'cooking_time',
            'servings',
            'is_public',
        ]));

        if ($request->has('categories')) {
            $recipe->categories()->sync($request->input('categories'));
        }

        if ($request->has('ingredients')) {
            $recipe->ingredients()->sync(
                collect($request->input('ingredients'))
                    ->mapWithKeys(fn (array $ingredient): array => [
                        $ingredient['ingredient_id'] => [
                            'amount' => $ingredient['amount'],
                            'unit' => $ingredient['unit'],
                        ],
                    ])
                    ->toArray()
            );
        }

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully.');
    }

    public function destroy(Recipe $recipe, DeleteRecipeAction $deleteRecipeAction): RedirectResponse
    {
        $this->authorize('delete', $recipe);

        $deleteRecipeAction->execute($recipe);

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully.');
    }
}
