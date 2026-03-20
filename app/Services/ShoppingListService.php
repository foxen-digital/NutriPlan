<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Models\MealPlan;
use App\Models\MealPlanDay;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Http\Resources\ShoppingListResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ShoppingListService
{
    public function __construct(
        private UnitConversionService $unitConversionService,
    ) {
    }
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
        $ingredientsWithQuantity = [];
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

                // First pass: collect all ingredients with quantities
                foreach ($recipe->ingredients as $ingredient) {
                    if ($ingredient->pivot->amount !== null) {
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

                        // Track that this ingredient has a quantity version
                        $ingredientsWithQuantity[$ingredient->id] = true;
                    }
                }

                // Second pass: collect ingredients without quantities, but only if no quantity version exists
                foreach ($recipe->ingredients as $ingredient) {
                    if ($ingredient->pivot->amount === null && !isset($ingredientsWithQuantity[$ingredient->id])) {
                        $unit = $ingredient->pivot->unit;
                        $unitValue = $unit ? ($unit instanceof MeasurementUnit ? $unit->value : $unit) : null;

                        // Use ingredient ID and unit as key
                        $key = $ingredient->id . '|' . ($unitValue ?? 'null');

                        // Only add if we don't already have this item
                        if (!isset($ingredients[$key])) {
                            $ingredients[$key] = [
                                'ingredient_id' => $ingredient->id,
                                'name' => $ingredient->name,
                                'quantity' => null,
                                'unit' => $unitValue,
                            ];
                        }
                    }
                }
            }
        }

        // === CONVERSION PASS (Story 1.3) ===
        $ingredients = $this->applyUnitConsolidation(
            $ingredients,
            $mealPlan->user->unitSystem()
        );

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

    /**
     * Prepare shopping list data for display, handling ordering and categorization.
     *
     * @param ShoppingList $shoppingList The shopping list to prepare
     * @return array The formatted shopping list data for the frontend
     */
    public function prepareForDisplay(ShoppingList $shoppingList): array
    {
        // Load items ordered by the order column, then by name as a fallback
        $shoppingList->load(['items' => function (HasMany $query): void {
            $query->orderBy('order')->orderBy('name');
        }]);

        // Group items by category while preserving their order
        $itemsByCategory = collect();
        $categories = $shoppingList->items->pluck('category')->unique();

        foreach ($categories as $category) {
            $categoryName = $category ?? 'Uncategorized';
            $itemsByCategory[$categoryName] = $shoppingList->items
                ->where('category', $category)
                ->values()
                ->all();
        }

        // Check if all items are uncategorized
        $allUncategorized = $itemsByCategory->keys()->count() === 1 && $itemsByCategory->has('Uncategorized');

        // Prepare shopping list data
        $shoppingListData = (new ShoppingListResource($shoppingList))->toArray(request());

        if ($allUncategorized) {
            // If all items are uncategorized, don't use categories
            $shoppingListData['use_categories'] = false;
        } else {
            // Use categories but ensure Uncategorized is always last
            $shoppingListData['use_categories'] = true;

            // Sort categories alphabetically
            $sorted = $itemsByCategory->sortKeys();

            // If 'Uncategorized' exists, move it to the end
            if ($sorted->has('Uncategorized')) {
                $uncategorized = $sorted->pull('Uncategorized');
                $sorted->put('Uncategorized', $uncategorized);
            }

            $shoppingListData['items_by_category'] = $sorted;
        }

        return $shoppingListData;
    }

    /**
     * Consolidate ingredient entries with compatible units into single entries.
     *
     * @param array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}> $ingredients
     * @param UnitSystem $unitSystem The user's preferred unit system
     * @return array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}>
     */
    private function applyUnitConsolidation(array $ingredients, UnitSystem $unitSystem): array
    {
        // Group by ingredient_id first
        $byIngredient = [];
        foreach ($ingredients as $key => $entry) {
            $ingredientId = $entry['ingredient_id'];
            $byIngredient[$ingredientId][$key] = $entry;
        }

        $result = [];

        foreach ($byIngredient as $ingredientId => $entries) {
            // Single entry = no consolidation needed
            if (count($entries) === 1) {
                $result += $entries;
                continue;
            }

            // Group entries by dimension (volume, weight, unknown)
            $byDimension = $this->groupEntriesByDimension($entries);

            foreach ($byDimension as $dimension => $dimensionEntries) {
                if ($dimension === 'unknown') {
                    // Unknown/null units: pass through unchanged
                    $result += $dimensionEntries;
                    continue;
                }

                if (count($dimensionEntries) === 1) {
                    // Only one entry in this dimension: pass through unchanged
                    $result += $dimensionEntries;
                    continue;
                }

                // Multiple same-dimension entries: consolidate
                $consolidated = $this->consolidateSameDimension(
                    $dimensionEntries,
                    $dimension,
                    $unitSystem
                );

                foreach ($consolidated as $entry) {
                    $key = $entry['ingredient_id'] . '|' . ($entry['unit'] ?? 'null');
                    $result[$key] = $entry;
                }
            }
        }

        return $result;
    }

    /**
     * Group ingredient entries by their measurement dimension.
     *
     * @param array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}> $entries
     * @return array<string, array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}>>
     */
    private function groupEntriesByDimension(array $entries): array
    {
        $byDimension = ['volume' => [], 'weight' => [], 'unknown' => []];

        foreach ($entries as $key => $entry) {
            $unit = $entry['unit'] !== null
                ? MeasurementUnit::tryFrom($entry['unit'])
                : null;

            if ($unit === null) {
                $byDimension['unknown'][$key] = $entry;
            } elseif ($unit->isVolume()) {
                $byDimension['volume'][$key] = $entry;
            } elseif ($unit->isWeight()) {
                $byDimension['weight'][$key] = $entry;
            } else {
                // Dimensionless units (PIECE, PINCH, CLOVE) are unknown for consolidation
                $byDimension['unknown'][$key] = $entry;
            }
        }

        // Remove empty dimensions
        return array_filter($byDimension);
    }

    /**
     * Consolidate same-dimension entries into a single entry in the preferred unit.
     *
     * @param array<string, array{ingredient_id: int, name: string, quantity: float, unit: string|null}> $entries
     * @param string $dimension 'volume' or 'weight'
     * @param UnitSystem $unitSystem
     * @return array<int, array{ingredient_id: int, name: string, quantity: float, unit: string|null}>
     */
    private function consolidateSameDimension(array $entries, string $dimension, UnitSystem $unitSystem): array
    {
        // Get first entry for name/ingredient_id
        $firstEntry = reset($entries);

        // Determine target unit from first entry's unit
        $firstUnit = MeasurementUnit::tryFrom($firstEntry['unit']);
        if ($firstUnit === null) {
            // Should not happen (already filtered), but pass through as safety
            return array_values($entries);
        }

        $targetUnit = $this->unitConversionService->preferredUnit($firstUnit, $unitSystem);
        if ($targetUnit === null) {
            // No preferred unit (dimensionless): pass through unchanged
            return array_values($entries);
        }

        // Accumulate all amounts converted to target unit
        $totalAmount = 0.0;
        $hasConvertible = false;
        $preservedEntries = [];
        foreach ($entries as $originalKey => $entry) {
            if ($entry['quantity'] === null) {
                // Null-quantity entries cannot be consolidated: preserve unchanged
                $preservedEntries[$originalKey] = $entry;
                continue;
            }

            $sourceUnit = MeasurementUnit::tryFrom($entry['unit']);
            if ($sourceUnit === null) {
                // Should not happen (already filtered), but preserve as safety
                $preservedEntries[$originalKey] = $entry;
                continue;
            }

            $converted = $this->unitConversionService->convert(
                $entry['quantity'],
                $sourceUnit,
                $targetUnit
            );

            if ($converted !== null) {
                $totalAmount += $converted;
                $hasConvertible = true;
            } else {
                // Unconvertible: preserve as separate entry
                $preservedEntries[$originalKey] = $entry;
            }
        }

        $result = array_values($preservedEntries);

        if ($hasConvertible) {
            // Apply ceiling rounding ONCE to final total
            $roundedAmount = $this->unitConversionService->applyCeilingRounding(
                $totalAmount,
                $targetUnit,
                $unitSystem
            );

            $result[] = [
                'ingredient_id' => $firstEntry['ingredient_id'],
                'name' => $firstEntry['name'],
                'quantity' => $roundedAmount,
                'unit' => $targetUnit->value,
            ];
        }

        return $result;
    }
}
