<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MealPlan;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MealPlanRecipeController extends Controller
{
    /**
     * Add a recipe to a meal plan.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'meal_plan_id' => 'required|exists:meal_plans,id',
            'recipe_id' => 'required|exists:recipes,id',
            'scale_factor' => 'nullable|numeric|min:0.01|max:100',
        ]);

        $mealPlan = MealPlan::findOrFail($validated['meal_plan_id']);

        // Check if user is authorized to update this meal plan
        Gate::authorize('update', $mealPlan);

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
        ]);
    }

    /**
     * Remove a recipe from a meal plan.
     */
    public function destroy(string $id, string $recipeId): RedirectResponse
    {
        // Debug info
        \Log::info('MealPlanRecipeController@destroy called', [
            'mealPlanId' => $id,
            'recipeId' => $recipeId,
        ]);

        $mealPlan = MealPlan::findOrFail($id);
        $recipe = Recipe::findOrFail($recipeId);

        Gate::authorize('update', $mealPlan);

        $mealPlan->recipes()->detach($recipe->id);

        return back()->with('success', 'Recipe removed from meal plan successfully.');
    }
}
