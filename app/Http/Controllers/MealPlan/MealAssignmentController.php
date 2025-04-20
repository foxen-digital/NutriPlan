<?php

declare(strict_types=1);

namespace App\Http\Controllers\MealPlan;

use App\Concerns\RecalculatesToCookFlags;
use App\Http\Controllers\Controller;
use App\Models\MealAssignment;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MealAssignmentController extends Controller
{
    use RecalculatesToCookFlags;

    /**
     * Store a newly created resource in storage.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'meal_plan_day_id' => 'required|exists:meal_plan_days,id',
            'meal_plan_recipe_id' => 'required|exists:meal_plan_recipe,id',
            'servings' => 'required|numeric|min:0.1|max:20',
            'to_cook' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $mealPlanDay = MealPlanDay::findOrFail($validated['meal_plan_day_id']);
            $mealPlanRecipe = MealPlanRecipe::findOrFail($validated['meal_plan_recipe_id']);

            // Check if this meal plan recipe is already assigned to this day
            if ($mealPlanDay->mealAssignments->contains('meal_plan_recipe_id', $mealPlanRecipe->id)) {
                throw ValidationException::withMessages([
                    'meal_plan_recipe_id' => ['This recipe is already assigned to this day.'],
                ]);
            }

            // Check if there are enough servings available
            if ($mealPlanRecipe->servings_available < $validated['servings']) {
                throw ValidationException::withMessages([
                    'servings' => ['Not enough servings available.'],
                ]);
            }

            // Find all existing assignments for this recipe in the meal plan
            $existingAssignments = MealAssignment::whereHas('mealPlanDay', function (Builder $query) use ($mealPlanDay): void {
                $query->where('meal_plan_id', $mealPlanDay->meal_plan_id);
            })->where('meal_plan_recipe_id', $mealPlanRecipe->id)
              ->with('mealPlanDay')
              ->get();

            // Create the assignment
            $assignment = new MealAssignment([
                'meal_plan_day_id' => $validated['meal_plan_day_id'],
                'meal_plan_recipe_id' => $validated['meal_plan_recipe_id'],
                'servings' => $validated['servings'],
                'to_cook' => $validated['to_cook']
            ]);
            $assignment->save();

            // Update available servings
            $mealPlanRecipe->servings_available -= $validated['servings'];
            $mealPlanRecipe->save();

            if (!$validated['to_cook']) {
                // If this assignment is not to cook, recalculate to_cook flags for all assignments
                $this->recalculateToCookFlags($assignment);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Meal assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return redirect()->back()->withErrors(['error' => 'Failed to assign meal. Please try again.']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws ValidationException
     */
    public function update(Request $request, MealAssignment $mealAssignment): RedirectResponse
    {
        $validated = $request->validate([
            'servings' => 'required|numeric|min:0.1|max:20',
            'to_cook' => 'sometimes|boolean',
        ]);

        try {
            DB::beginTransaction();

            $mealPlanRecipe = $mealAssignment->mealPlanRecipe;
            $servingsDiff = $validated['servings'] - $mealAssignment->servings;

            // Check if there are enough servings available for the increase
            if ($servingsDiff > 0 && $mealPlanRecipe->servings_available < $servingsDiff) {
                throw ValidationException::withMessages([
                    'servings' => ['Not enough servings available for the increase.'],
                ]);
            }

            // Update available servings
            $mealPlanRecipe->servings_available -= $servingsDiff;
            $mealPlanRecipe->save();

            // Update the assignment
            $mealAssignment->servings = $validated['servings'];
            if (isset($validated['to_cook'])) {
                $mealAssignment->to_cook = $validated['to_cook'];
            }
            $mealAssignment->save();

            DB::commit();

            return redirect()->back()->with('success', 'Meal assignment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }
            report($e);
            return redirect()->back()->withErrors(['error' => 'Failed to update meal assignment. Please try again.']);
        }
    }

    /**
     * Toggle the to_cook status for a meal assignment.
     */
    public function toggleToCook(MealAssignment $mealAssignment): JsonResponse
    {
        try {
            $mealAssignment->to_cook = !$mealAssignment->to_cook;
            $mealAssignment->save();

            return response()->json([
                'success' => true,
                'to_cook' => $mealAssignment->to_cook,
                'message' => 'Cooking status toggled successfully.'
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle cooking status.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MealAssignment $mealAssignment): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Return servings to available pool
            $mealPlanRecipe = $mealAssignment->mealPlanRecipe;
            $mealPlanRecipe->servings_available += $mealAssignment->servings;
            $mealPlanRecipe->save();

            // Get meal plan and day information before deleting
            $mealPlanDay = $mealAssignment->mealPlanDay;
            $mealPlanId = $mealPlanDay->meal_plan_id;
            $recipeId = $mealAssignment->meal_plan_recipe_id;
            $wasToCook = $mealAssignment->to_cook;

            // Delete the assignment
            $mealAssignment->delete();

            // If this was marked as "to cook", find the next earliest assignment and mark it
            if ($wasToCook) {
                $earliestAssignment = MealAssignment::whereHas('mealPlanDay', function (Builder $query) use ($mealPlanId): void {
                    $query->where('meal_plan_id', $mealPlanId);
                })
                ->where('meal_plan_recipe_id', $recipeId)
                ->with('mealPlanDay')
                ->get()
                ->sortBy(fn (MealAssignment $assignment) => $assignment->mealPlanDay->day_number)
                ->first();

                if ($earliestAssignment) {
                    $earliestAssignment->to_cook = true;
                    $earliestAssignment->save();
                }
            }

            DB::commit();

            return redirect()->back()->with('success', 'Meal assignment removed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->back()->withErrors(['error' => 'Failed to remove meal assignment. Please try again.']);
        }
    }
}
