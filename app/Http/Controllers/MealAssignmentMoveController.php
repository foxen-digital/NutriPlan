<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MealAssignment;
use App\Models\MealPlanDay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MealAssignmentMoveController extends Controller
{
    /**
     * Move a meal assignment to a different day.
     *
     * @param Request $request
     * @param MealAssignment $mealAssignment
     * @return JsonResponse
     */
    public function __invoke(Request $request, MealAssignment $mealAssignment): JsonResponse
    {
        $validated = $request->validate([
            'new_meal_plan_day_id' => ['required', 'integer', 'exists:meal_plan_days,id'],
        ]);

        // Authorize the user can update this meal assignment
        Gate::authorize('update', $mealAssignment);

        $newDay = MealPlanDay::findOrFail($validated['new_meal_plan_day_id']);
        
        // Ensure the new day belongs to the same meal plan as the current day
        if ($newDay->meal_plan_id !== $mealAssignment->mealPlanDay->meal_plan_id) {
            return response()->json(
                ['message' => 'Cannot move assignment to a day in a different meal plan'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Check if there's already an assignment for this recipe on the target day
        $existingAssignment = MealAssignment::where('meal_plan_day_id', $newDay->id)
            ->where('meal_plan_recipe_id', $mealAssignment->meal_plan_recipe_id)
            ->first();

        if ($existingAssignment) {
            return response()->json(
                ['message' => 'This recipe is already assigned to the target day'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Update the meal assignment with the new day
        $mealAssignment->meal_plan_day_id = $validated['new_meal_plan_day_id'];
        $mealAssignment->save();

        // Get all assignments for this recipe in the meal plan and recalculate to_cook flags
        $this->recalculateToCookFlags($mealAssignment);

        return response()->json(['message' => 'Meal assignment moved successfully'], Response::HTTP_OK);
    }

    /**
     * Recalculate to_cook flags for all assignments of the same recipe.
     *
     * @param MealAssignment $mealAssignment
     * @return void
     */
    private function recalculateToCookFlags(MealAssignment $mealAssignment): void
    {
        // Find the meal plan ID
        $mealPlanId = $mealAssignment->mealPlanDay->meal_plan_id;
        $mealPlanRecipeId = $mealAssignment->meal_plan_recipe_id;

        // Get all assignments for this recipe in the meal plan, ordered by day
        $relatedAssignments = MealAssignment::with('mealPlanDay')
            ->whereHas('mealPlanDay', function ($query) use ($mealPlanId) {
                $query->where('meal_plan_id', $mealPlanId);
            })
            ->where('meal_plan_recipe_id', $mealPlanRecipeId)
            ->get()
            ->sortBy(function ($assignment) {
                return $assignment->mealPlanDay->day_number;
            });

        // Mark only the first assignment as to_cook, rest as false
        DB::transaction(function () use ($relatedAssignments) {
            foreach ($relatedAssignments as $index => $assignment) {
                $assignment->to_cook = ($index === 0);
                $assignment->save();
            }
        });
    }
} 