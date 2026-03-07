<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MealAssignment;

class ShoppingListSyncService
{
    /**
     * Sync a new meal assignment to affected shopping lists.
     *
     * This method will be implemented in Story 1.3 to:
     * - Find shopping lists associated with the meal plan
     * - Add ingredients from the meal to the shopping lists
     *
     * @param  MealAssignment  $meal  The newly created meal assignment
     */
    public function syncNewMeal(MealAssignment $meal): void
    {
        // Implementation will be added in Story 1.3: ShoppingListSyncService
        // This stub exists so the observer can invoke it and tests can mock it
    }
}
