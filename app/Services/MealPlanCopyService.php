<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MealAssignment;
use App\Models\MealPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MealPlanCopyService
{
    /**
     * Copy a meal plan with its associated recipes and meal assignments.
     *
     * @param MealPlan $mealPlan The source meal plan to copy
     * @param User $user The user who will own the new meal plan
     * @param array $data The data for the new meal plan
     * @return MealPlan The newly created meal plan
     */
    public function copy(MealPlan $mealPlan, User $user, array $data): MealPlan
    {
        return DB::transaction(function () use ($mealPlan, $user, $data) {
            try {
                // Extract data with defaults
                $name = $data['name'] ?? ($mealPlan->name ? 'Copy of ' . $mealPlan->name : null);
                $startDate = $data['start_date'];
                $peopleCount = $data['people_count'] ?? $mealPlan->people_count;

                // Create a new meal plan
                $newMealPlan = $user->mealPlans()->create([
                    'name' => $name,
                    'start_date' => $startDate,
                    'duration' => $mealPlan->duration,
                    'people_count' => $peopleCount,
                ]);

                // Create meal plan days
                for ($i = 1; $i <= $newMealPlan->duration; $i++) {
                    $newMealPlan->days()->create(['day_number' => $i]);
                }

                // Load the original plan's data
                $mealPlan->load([
                    'recipes',
                    'days.mealAssignments.mealPlanRecipe.recipe'
                ]);
                $newMealPlan->load(['days']);

                // Copy recipes from the original plan to the new plan
                foreach ($mealPlan->recipes as $recipe) {
                    $pivot = $recipe->pivot;

                    // Create a new meal plan recipe entry
                    $newMealPlan->recipes()->attach($recipe->id, [
                        'scale_factor' => $pivot->scale_factor,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Re-fetch the new plan with recipes to get fresh pivot data
                $newMealPlan->load(['recipes', 'days']);

                // Copy meal assignments from original plan days to new plan days
                foreach ($mealPlan->days as $day) {
                    // Find the corresponding day in the new plan
                    $newDay = $newMealPlan->days()->where('day_number', $day->day_number)->first();

                    if (!$newDay) {
                        continue;
                    }

                    foreach ($day->mealAssignments as $assignment) {
                        /** @var MealAssignment $assignment */
                        // Find the corresponding recipe in the new plan
                        $originalPlanRecipe = $assignment->mealPlanRecipe;
                        if (!$originalPlanRecipe) {
                            continue;
                        }
                        if (!$originalPlanRecipe->recipe) {
                            continue;
                        }

                        $pivotId = null;

                        // Find the corresponding recipe pivot in the new plan
                        foreach ($newMealPlan->recipes as $newPlanRecipe) {
                            if ($newPlanRecipe->id === $originalPlanRecipe->recipe->id) {
                                $pivotId = $newPlanRecipe->pivot->id;
                                break;
                            }
                        }

                        if (!$pivotId) {
                            continue;
                        }

                        // Create a new meal assignment with the same properties
                        $newDay->mealAssignments()->create([
                            'meal_plan_recipe_id' => $pivotId,
                            'servings' => $assignment->servings,
                            'to_cook' => $assignment->to_cook,
                        ]);
                    }
                }

                Log::info('Meal plan copied successfully', [
                    'original_id' => $mealPlan->id,
                    'new_id' => $newMealPlan->id,
                    'user_id' => $user->id,
                ]);

                return $newMealPlan;
            } catch (\Exception $e) {
                Log::error('Failed to copy meal plan', [
                    'original_id' => $mealPlan->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }
}
