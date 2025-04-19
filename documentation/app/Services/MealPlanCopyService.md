# Documentation: MealPlanCopyService.php

Original file: `app/Services/MealPlanCopyService.php`

# MealPlanCopyService Documentation

## Table of Contents

- [Introduction](#introduction)
- [copy() Method](#copy-method)

## Introduction

The `MealPlanCopyService` class is part of the App\Services namespace in the NutriPlan application. This service is responsible for duplicating existing meal plans along with their associated recipes and meal assignments. It leverages Laravel's powerful database transaction handling to ensure that the operation is atomic, meaning that if any part of the process fails, all changes can be rolled back to maintain data integrity. By isolating the copy functionality, this service supports better code organization, testability, and maintenance.

## copy() Method

The `copy()` method facilitates the duplication of a specified meal plan.

### Purpose

This method accepts an existing meal plan and creates a new meal plan for a specified user, including copying all relevant recipes and meal assignments.

### Parameters

| Parameter   | Type     | Description                                                                                           |
|-------------|----------|-------------------------------------------------------------------------------------------------------|
| `$mealPlan` | MealPlan | The source meal plan to be copied. This is the MealPlan instance that contains the original data.     |
| `$user`     | User     | The user who will be the owner of the new meal plan. This allows the system to maintain user-specific data. |
| `$data`     | array    | An associative array containing data for the new meal plan. This can include the meal plan name, start date, and people count. |

### Return Value

- Returns a `MealPlan` instance representing the newly created meal plan.

### Functionality

1. **Database Transaction**: The method utilizes Laravel's `DB::transaction()` to ensure that all operations are executed within a single transaction. If any step fails, all changes will be rolled back.

2. **Data Extraction**: It extracts data from the provided `$data` array, enabling default values from the original meal plan if certain keys are not present. Specifically:
   - The new meal plan name is set to "Copy of [Original Name]" by default.
   - The start date is directly taken from the `$data`.
   - It defaults people count to the original meal plan's people count if not specified.

3. **Meal Plan Creation**: A new meal plan is created using the `create()` method from the user's `mealPlans()` relationship.

4. **Day Creation**: It creates entries for the number of days specified in the new meal plan’s duration.

5. **Loading Original Data**: The method loads the related recipes and meal assignments from the original meal plan to facilitate copying.

6. **Recipe Duplication**: For each recipe in the original plan, it creates a corresponding entry in the new meal plan. It also copies the associated `scale_factor` for each recipe.

7. **Meal Assignment Duplication**: The method iterates over the days of the original meal plan to find corresponding days in the new meal plan. For each meal assignment in the original, it creates a new assignment in the new plan, linking to the duplicated recipes.

8. **Logging**: Upon successful copying, it logs the operation details for audit purposes. If an error occurs, it logs the error information to assist with debugging.

Here's the method implementation for reference:

```php
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
```

This documentation provides an overview of the `MealPlanCopyService` class and its `copy()` method, detailing its purpose, parameters, return value,