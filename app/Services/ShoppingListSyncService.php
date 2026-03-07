<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\UpdateShoppingListJob;
use App\Models\MealAssignment;
use App\Models\ShoppingList;
use Illuminate\Support\Facades\Log;

class ShoppingListSyncService
{
    /**
     * Sync a new meal assignment to affected shopping lists.
     *
     * This method finds all shopping lists associated with the meal plan
     * and dispatches background jobs to update each list's ingredients.
     *
     * @param  MealAssignment  $meal  The newly created meal assignment
     */
    public function syncNewMeal(MealAssignment $meal): void
    {
        $mealPlan = $meal->mealPlanDay?->mealPlan;

        if ($mealPlan === null) {
            Log::warning('ShoppingListSyncService: meal plan relationship not available', [
                'meal_assignment_id' => $meal->id,
            ]);

            return;
        }

        $mealPlanId = $mealPlan->id;

        $shoppingLists = ShoppingList::where('meal_plan_id', $mealPlanId)
            ->where('user_id', $mealPlan->user_id)
            ->get();

        if ($shoppingLists->isEmpty()) {
            Log::info('No shopping lists to sync for meal plan', [
                'meal_assignment_id' => $meal->id,
                'meal_plan_id' => $mealPlanId,
            ]);

            return;
        }

        Log::info('Shopping list sync initiated', [
            'meal_assignment_id' => $meal->id,
            'meal_plan_id' => $mealPlanId,
            'affected_lists' => $shoppingLists->count(),
        ]);

        foreach ($shoppingLists as $shoppingList) {
            UpdateShoppingListJob::dispatch($shoppingList, $meal);
        }
    }
}
