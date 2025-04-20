<?php

declare(strict_types=1);

namespace App\Http\Controllers\MealPlan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMealPlanRecipeRequest;
use App\Models\MealPlan;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MealPlanRecipeController extends Controller
{
    /**
     * Add a recipe to a meal plan.
     */
    public function store(StoreMealPlanRecipeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $mealPlan = MealPlan::findOrFail($validated['meal_plan_id']);
        $recipeId = $validated['recipe_id'];
        $scaleFactor = $validated['scale_factor'] ?? 1.0;

        // Check if the recipe is already in the meal plan
        if (!$mealPlan->recipes()->where('recipe_id', $recipeId)->exists()) {
            $mealPlan->recipes()->attach($recipeId, [
                'scale_factor' => $scaleFactor,
            ]);

            // Calculate available servings
            $mealPlanRecipe = $mealPlan->recipes()->where('recipe_id', $recipeId)->first();
            if ($mealPlanRecipe) {
                $mealPlanRecipe->pivot->calculateAvailableServings();
                $mealPlanRecipe->pivot->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Recipe added to meal plan successfully.'
        ], Response::HTTP_OK);
    }

    /**
     * Remove a recipe from a meal plan.
     */
    public function destroy(MealPlan $mealPlan, Recipe $recipe): RedirectResponse
    {
        \Log::debug('Destroying recipe', [
            'mealPlanId' => $mealPlan->id,
            'recipeId' => $recipe->id
        ]);

        Gate::authorize('update', $mealPlan);

        $mealPlan->recipes()->detach($recipe->id);

        return back()->with('success', 'Recipe removed from meal plan successfully.');
    }
}
