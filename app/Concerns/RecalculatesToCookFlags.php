<?php

namespace App\Concerns;

use App\Models\MealAssignment;
use Illuminate\Support\Facades\DB;

trait RecalculatesToCookFlags
{
    /**
     * Recalculate to_cook flags for all assignments of the same recipe.
     *
     * @param MealAssignment $mealAssignment The assignment that was just moved (used to identify recipe and plan)
     */
    private function recalculateToCookFlags(MealAssignment $mealAssignment): void
    {
        $mealPlanId = $mealAssignment->mealPlanDay->meal_plan_id;
        $mealPlanRecipeId = $mealAssignment->meal_plan_recipe_id;

        // Get all assignments for this recipe in the meal plan, ordered by day number in the DB
        $relatedAssignments = MealAssignment::query()
            ->join('meal_plan_days', 'meal_assignments.meal_plan_day_id', '=', 'meal_plan_days.id')
            ->where('meal_plan_days.meal_plan_id', $mealPlanId)
            ->where('meal_assignments.meal_plan_recipe_id', $mealPlanRecipeId)
            ->select('meal_assignments.*') // Select only columns from meal_assignments to avoid conflicts
            ->orderBy('meal_plan_days.day_number', 'asc')
            ->get();

        // Mark only the first assignment as to_cook, rest as false
        DB::transaction(function () use ($relatedAssignments): void {
            foreach ($relatedAssignments as $index => $assignmentToUpdate) {
                // Fetch the model instance to ensure we have a proper Eloquent model
                $assignmentModel = MealAssignment::find($assignmentToUpdate->id);
                if ($assignmentModel) {
                    $assignmentModel->to_cook = ($index === 0);
                    $assignmentModel->save();
                }
            }
        });
    }
}
