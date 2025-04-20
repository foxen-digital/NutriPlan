<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MealAssignment;
use App\Models\User;

class MealAssignmentPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MealAssignment $mealAssignment): bool
    {
        // User can view if they own the meal plan
        return $user->id === $mealAssignment->mealPlanDay->mealPlan->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MealAssignment $mealAssignment): bool
    {
        // User can update if they own the meal plan
        return $user->id === $mealAssignment->mealPlanDay->mealPlan->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MealAssignment $mealAssignment): bool
    {
        // User can delete if they own the meal plan
        return $user->id === $mealAssignment->mealPlanDay->mealPlan->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // By default, all authenticated users can create assignments
        // The specific meal plan ownership will be checked in the controller
        return true;
    }
}
