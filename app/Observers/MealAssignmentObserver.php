<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MealAssignment;
use App\Services\ShoppingListSyncService;

class MealAssignmentObserver
{
    /**
     * Handle the MealAssignment "created" event.
     *
     * Triggers shopping list synchronization when a new meal assignment
     * is created with to_cook = true. Only fires on creation, not updates.
     *
     * @param  MealAssignment  $mealAssignment  The newly created meal assignment
     */
    public function created(MealAssignment $mealAssignment): void
    {
        // Only sync if the meal is marked for cooking
        if (! $mealAssignment->to_cook) {
            return;
        }

        // Eager load relationships to prevent N+1 queries
        $mealAssignment->load('mealPlanDay.mealPlan', 'mealPlanRecipe.recipe.ingredients');

        // Delegate to the sync service
        app(ShoppingListSyncService::class)->syncNewMeal($mealAssignment);
    }
}
