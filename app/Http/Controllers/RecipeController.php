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
use App\Jobs\NormalizeRecipeInstructionsJob;
use App\Services\RecipeIndexService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\Recipe\CreateRecipeRequest;
use App\Http\Requests\Recipe\UpdateRecipeRequest;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecipeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, RecipeIndexService $recipeService): Response
    {
        $user = $request->user();
        $filters = $request->only(['category', 'show_mine', 'search_term', 'search_mode']);

        // Ensure search_term is null if empty string to prevent unnecessary filtering
        if (isset($filters['search_term']) && trim($filters['search_term']) === '') {
            $filters['search_term'] = null;
        }

        $recipes = $recipeService->getRecipes($user, $filters);

        // Append all query parameters (including pagination, category, show_mine, search) to pagination links
        $recipes->appends($request->query());

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'filter' => $filters, // Pass all filters including search
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
        $recipeData = $request->only([
            'title',
            'description',
            'instructions',
            'prep_time',
            'cooking_time',
            'servings',
            'is_public',
        ]);

        if ($request->hasFile('images')) {
            $recipeData['images'] = $this->storeRecipeImages($request->file('images'));
        }

        $recipe = $request->user()->recipes()->create($recipeData);

        // Queue background formatting of the instructions; the user is notified when it completes.
        NormalizeRecipeInstructionsJob::dispatch($recipe->id, $request->user()->id);

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
            ->with('success', "Recipe created successfully. Instructions are being formatted in the background - you'll be notified when it's ready.");
    }

    public function show(Recipe $recipe): Response
    {
        $this->authorize('view', $recipe);

        $user = request()->user();
        $recipe->load([
            'user:id,name,slug',
            'categories' => function (BelongsToMany $query): void {
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
            'categories' => function (BelongsToMany $query): void {
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

        $recipeData = $request->safe([
            'title',
            'description',
            'instructions',
            'prep_time',
            'cooking_time',
            'servings',
            'is_public',
        ]);

        $recipe->update($recipeData);

        // Capture this before syncing images below; wasChanged only reflects the most recent save.
        $instructionsChanged = $recipe->wasChanged('instructions');

        // Replace the recipe's images with the form's final list: URLs the user kept
        // plus newly uploaded files. Kept URLs must already be attached to the recipe
        // so a tampered request cannot inject arbitrary values.
        $keptImages = collect($request->input('existing_images', []))
            ->intersect($recipe->images ?? [])
            ->values()
            ->all();

        $newImages = $request->hasFile('images')
            ? $this->storeRecipeImages($request->file('images'))
            : [];

        $this->deleteRecipeImages(collect($recipe->images ?? [])->diff($keptImages));

        $recipe->images = [...$keptImages, ...$newImages];
        $recipe->save();

        // Only queue background formatting when the instructions actually changed;
        // a title-only edit should not trigger an LLM call.
        if ($instructionsChanged) {
            NormalizeRecipeInstructionsJob::dispatch($recipe->id, $request->user()->id);
        }

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

        $message = $instructionsChanged
            ? "Recipe updated successfully. Instructions are being formatted in the background - you'll be notified when it's ready."
            : 'Recipe updated successfully.';

        return redirect()->route('recipes.show', $recipe)
            ->with('success', $message);
    }

    public function destroy(Recipe $recipe, DeleteRecipeAction $deleteRecipeAction): RedirectResponse
    {
        $this->authorize('delete', $recipe);

        $deleteRecipeAction->execute($recipe);

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully.');
    }

    /**
     * Store uploaded images on the public disk and return their relative URLs.
     *
     * @param  array<int, UploadedFile>  $images
     * @return list<string>
     */
    private function storeRecipeImages(array $images): array
    {
        return collect($images)
            ->map(fn (UploadedFile $image): string => '/storage/'.$image->storePublicly('recipes', 'public'))
            ->all();
    }

    /**
     * Delete the local files behind images that are no longer attached to the recipe.
     * External URLs (e.g. from imports) are left untouched.
     *
     * @param  iterable<int, mixed>  $images
     */
    private function deleteRecipeImages(iterable $images): void
    {
        foreach ($images as $image) {
            if (is_string($image) && str_starts_with($image, '/storage/')) {
                Storage::disk('public')->delete(Str::after($image, '/storage/'));
            }
        }
    }
}
