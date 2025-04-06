<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MeasurementUnit;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ShoppingListService
{
    /**
     * Generate a Shopping List from a Meal Plan for a specific period.
     *
     * @param MealPlan $mealPlan The source meal plan.
     * @param string $name Name for the new list.
     * @param string $period "full", "week1", or "week2".
     * @return ShoppingList The newly generated shopping list.
     */
    public function generateFromMealPlan(MealPlan $mealPlan, string $name, string $period): ShoppingList
    {
        // 1. Determine start/end dates based on mealPlan start and period
        $dates = $this->calculatePeriodDates($mealPlan, $period);
        $startDate = $dates['start_date'];
        $endDate = $dates['end_date'];

        // 2. Create the shopping list
        $shoppingList = new ShoppingList([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        $shoppingList->user_id = $mealPlan->user_id;
        $shoppingList->save();

        // 3. Fetch relevant MealAssignments (within dates, to_cook=true)
        /** @var Collection<int, MealPlanDay> $days */
        $days = $mealPlan->days()
            ->whereHas('mealAssignments', function (Builder $query): void {
                $query->where('to_cook', true);
            })
            ->with([
                'mealAssignments' => function (HasMany $query): void {
                    $query->where('to_cook', true)
                        ->with(['mealPlanRecipe.recipe.ingredients']);
                }
            ])
            ->get();

        // 4. Extract all ingredients from assignments
        /** @var array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}> $ingredients */
        $ingredients = [];
        foreach ($days as $day) {
            // Skip days outside our date range
            $dayDate = Carbon::parse($day->date);
            if ($dayDate->lt($startDate)) {
                continue;
            }
            if ($dayDate->gt($endDate)) {
                continue;
            }

            foreach ($day->mealAssignments as $assignment) {
                $recipe = $assignment->mealPlanRecipe->recipe;
                $scale = $assignment->mealPlanRecipe->scale_factor;

                foreach ($recipe->ingredients as $ingredient) {
                    $amount = $ingredient->pivot->amount * $scale;
                    $unit = $ingredient->pivot->unit;
                    $unitValue = $unit ? ($unit instanceof MeasurementUnit ? $unit->value : $unit) : null;

                    // Use ingredient ID and unit as key for consolidation
                    $key = $ingredient->id . '|' . ($unitValue ?? 'null');

                    if (!isset($ingredients[$key])) {
                        $ingredients[$key] = [
                            'ingredient_id' => $ingredient->id,
                            'name' => $ingredient->name,
                            'quantity' => $amount,
                            'unit' => $unitValue,
                        ];
                    } else {
                        $ingredients[$key]['quantity'] += $amount;
                    }
                }
            }
        }

        // 5. Create shopping list items
        foreach ($ingredients as $ingredient) {
            ShoppingListItem::create([
                'shopping_list_id' => $shoppingList->id,
                'ingredient_id' => $ingredient['ingredient_id'],
                'name' => $ingredient['name'],
                'quantity' => $ingredient['quantity'],
                'unit' => $ingredient['unit'],
                'is_custom' => false,
                'is_purchased' => false,
            ]);
        }

        return $shoppingList;
    }

    /**
     * Calculate start and end dates for a meal plan period.
     *
     * @param string $period "full", "week1", or "week2"
     * @return array{start_date: Carbon, end_date: Carbon}
     */
    private function calculatePeriodDates(MealPlan $mealPlan, string $period): array
    {
        $startDate = Carbon::parse($mealPlan->start_date);
        $endDate = Carbon::parse($mealPlan->start_date)->addDays($mealPlan->duration - 1);

        if ($period === 'week1') {
            $endDate = $startDate->copy()->addDays(6);
        } elseif ($period === 'week2') {
            $startDate = $startDate->copy()->addDays(7);
        }

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }
}
