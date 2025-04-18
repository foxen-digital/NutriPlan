<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource, filtered by categories with visible recipes.
     *
     * @param Request $request
     * @return InertiaResponse
     */
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();

        $categories = Category::query()
            ->withCount(['recipes' => function (Builder $query) use ($user): void {
                // Only count recipes that are public or owned by the current user
                $query->where(function (Builder $query) use ($user): void {
                    $query->where('is_public', true);

                    if ($user) {
                        $query->orWhere('user_id', $user->id);
                    }
                });
            }])
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->filter(fn (Category $category): bool => $category->recipes_count > 0)
            ->values();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::query()->create([
            'name' => $request->validated('name'),
            'is_active' => true,
        ]);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource's recipes.
     *
     * @param Category $category
     * @param Request $request
     * @return InertiaResponse
     */
    public function show(Category $category, Request $request): InertiaResponse
    {
        $user = $request->user();

        $recipes = Recipe::query()
            ->whereHas('categories', function (Builder $query) use ($category): void {
                $query->where('categories.id', $category->id);
            })
            ->where(function (Builder $query) use ($user): void {
                $query->where('is_public', true);

                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->with(['user:id,name,slug', 'categories'])
            ->latest()
            ->paginate(12);

        // Add is_favorited flag if user is logged in
        if ($user) {
            $recipes->getCollection()->transform(function (Recipe $recipe) use ($user): Recipe {
                $recipe->is_favorited = $user->favorites()->where('recipe_id', $recipe->id)->exists();
                return $recipe;
            });
        }

        return Inertia::render('Recipes/Index', [
            'recipes' => $recipes,
            'category' => new CategoryResource($category),
        ]);
    }
}
