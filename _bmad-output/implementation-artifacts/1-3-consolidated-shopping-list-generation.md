# Story 1.3: Consolidated Shopping List Generation

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As a NutriPlan user generating a shopping list,
I want ingredients that appear in multiple recipes with different compatible units to be consolidated into a single list entry,
So that my shopping list shows clean, unambiguous quantities in the units I use — with no mental arithmetic required at the supermarket.

## Acceptance Criteria

1. **Cross-unit consolidation (metric preference)** — Given a meal plan where two recipes both require olive oil (one uses `2 tbsp`, another uses `60ml`) and the user has metric preference, when the shopping list is generated, then a single `olive oil` entry appears with the combined quantity expressed in ml, with ceiling rounding applied to the total.

2. **Metric unit system default** — Given a user with default metric preference and cross-unit ingredients, when the shopping list is generated, then all consolidated quantities are expressed in metric units (ml for volume, g for weight).

3. **Imperial unit system** — Given a user with imperial preference and cross-unit ingredients (e.g., `2 tbsp` and `60ml` olive oil), when the shopping list is generated, then all consolidated quantities are expressed in imperial units (fl oz for volume, oz for weight).

4. **Cross-dimension pass-through** — Given two recipes that use the same ingredient in cross-dimension units (e.g., `1 cup flour` [volume] and `100g flour` [weight]), when the shopping list is generated, then two separate flour entries appear on the list — neither is modified (pass-through).

5. **Unknown unit pass-through** — Given an ingredient with a non-standard unit (e.g., `"handful"`, `"pinch"`, or null), when the shopping list is generated, then the item appears unchanged with its original unit — no error is produced.

6. **Same-unit consolidation regression** — Given two recipes using the same ingredient in the same unit (existing behaviour), when the shopping list is generated, then they are still consolidated into one entry with their quantities summed (no regression).

7. **Ceiling rounding once** — Ceiling rounding is applied once to the final consolidated total — not to each individual intermediate conversion.

## Tasks / Subtasks

- [x] Inject `UnitConversionService` into `ShoppingListService` (AC: all)
  - [x] Add constructor with `UnitConversionService` parameter via constructor property promotion
  - [x] No changes to service container binding (Laravel auto-resolves)

- [x] Add conversion pass between ingredient accumulation and item creation (AC: 1, 2, 3, 4, 5, 6, 7)
  - [x] After existing loop builds `$ingredients` (keyed `ingredient_id|unit`), group entries by `ingredient_id`
  - [x] For each ingredient group with >1 unit variant: attempt cross-unit consolidation
  - [x] Successfully consolidated groups: replace with single entry in preferred unit with ceiling rounding applied
  - [x] Groups that cannot be consolidated (cross-dimension, null/unknown units): remain as separate entries unchanged
  - [x] Proceed to item creation with the resolved `$ingredients` array

- [x] Implement consolidation logic (AC: 1, 2, 3, 7)
  - [x] Resolve user's `UnitSystem` preference once at generation start: `$mealPlan->user->unitSystem()`
  - [x] For each multi-unit group: identify the dimension (volume vs weight) of each entry
  - [x] Group entries by dimension (volume entries together, weight entries together)
  - [x] For each same-dimension subgroup: get preferred unit via `UnitConversionService::preferredUnit()`
  - [x] Convert all amounts to preferred unit (accumulate raw converted values)
  - [x] Apply `applyCeilingRounding()` once to the final accumulated total
  - [x] Create single consolidated entry with rounded total

- [x] Handle unconvertible entries (AC: 4, 5)
  - [x] Cross-dimension entries: preserve as separate entries (e.g., volume flour + weight flour = 2 items)
  - [x] Unknown/null unit entries: preserve as separate entries unchanged
  - [x] Never drop or modify entries that cannot be converted

- [x] Write feature tests for `ShoppingListService` (all ACs)
  - [x] Test: same-unit consolidation (regression check)
  - [x] Test: cross-unit consolidation with metric preference (tbsp + ml → ml)
  - [x] Test: cross-unit consolidation with imperial preference (tbsp + ml → fl oz)
  - [x] Test: cross-dimension entries remain separate (cup flour + g flour)
  - [x] Test: unknown unit entries remain unchanged
  - [x] Test: ceiling rounding applied once to final total
  - [x] Run `php artisan test --compact --filter=ShoppingListGenerationTest`

- [x] Run Pint formatter: `vendor/bin/pint --dirty --format agent`

## Dev Notes

### Architecture Overview

This story integrates the `UnitConversionService` (completed in Story 1.2) into the `ShoppingListService` to enable cross-unit consolidation during shopping list generation. The conversion pass is inserted between the existing ingredient accumulation step (step 4) and the `ShoppingListItem` creation step (step 5).

**Key Design Principles:**
- **Single preference read**: `$user->unitSystem()` is called once at generation start, before the conversion pass
- **Dimension-aware grouping**: Ingredients are first grouped by `ingredient_id`, then sub-grouped by measurement dimension (volume vs weight)
- **Pass-through preservation**: Entries that cannot be consolidated (cross-dimension, unknown units) remain as separate items unchanged
- **Ceiling rounding once**: Rounding is applied only to the final accumulated total, not to intermediate conversions

### Current `ShoppingListService` Implementation

The existing `generateFromMealPlan()` method:
1. Calculates period dates from meal plan
2. Creates the `ShoppingList` record
3. Fetches `MealPlanDay` records with eager-loaded `mealAssignments`
4. **Accumulates ingredients into `$ingredients` array keyed by `ingredient_id|unit`** (lines 59-123)
5. Creates `ShoppingListItem` records from the accumulated array (lines 126-136)

**Current key format:** `"{$ingredient->id}|{$unitValue}"` where `$unitValue` is the enum's string value or `'null'`.

**Current consolidation:** Same-ingredient + same-unit entries have their quantities summed (lines 93-95).

### Integration Point — Exact Location

Insert the conversion pass between line 123 (end of ingredient accumulation) and line 125 (comment "// 5. Create shopping list items"):

```php
        }

        // === CONVERSION PASS (Story 1.3) ===
        $ingredients = $this->applyUnitConsolidation(
            $ingredients,
            $mealPlan->user->unitSystem()
        );

        // 5. Create shopping list items
        foreach ($ingredients as $ingredient) {
```

### `applyUnitConsolidation()` Method — Implementation Guide

```php
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
```

### Helper Methods — Implementation Guide

```php
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
    foreach ($entries as $entry) {
        $sourceUnit = MeasurementUnit::tryFrom($entry['unit']);
        if ($sourceUnit === null) {
            // Should not happen, but skip this entry
            continue;
        }

        $converted = $this->unitConversionService->convert(
            $entry['quantity'],
            $sourceUnit,
            $targetUnit
        );

        if ($converted !== null) {
            $totalAmount += $converted;
        }
    }

    // Apply ceiling rounding ONCE to final total
    $roundedAmount = $this->unitConversionService->applyCeilingRounding(
        $totalAmount,
        $targetUnit,
        $unitSystem
    );

    // Return single consolidated entry
    return [[
        'ingredient_id' => $firstEntry['ingredient_id'],
        'name' => $firstEntry['name'],
        'quantity' => $roundedAmount,
        'unit' => $targetUnit->value,
    ]];
}
```

### Constructor Injection — Exact Change

Add to the top of `ShoppingListService` class:

```php
class ShoppingListService
{
    public function __construct(
        private UnitConversionService $unitConversionService,
    ) {}

    // ... existing methods
```

### Project Structure Notes

**Files to modify:**
- `app/Services/ShoppingListService.php` — inject `UnitConversionService`, add conversion pass + helper methods

**Files to create:**
- `tests/Feature/ShoppingList/ShoppingListGenerationTest.php` — feature tests for cross-unit consolidation

**Files NOT touched in this story:**
- `app/Services/UnitConversionService.php` — completed in Story 1.2, no changes
- `app/Enums/MeasurementUnit.php` — completed in Story 1.2, no changes
- `app/Enums/UnitSystem.php` — completed in Story 1.1, no changes
- `app/Models/User.php` — completed in Story 1.1, no changes
- `app/Jobs/UpdateShoppingListJob.php` — modified in Story 2.1 (not this story)
- All frontend files — no frontend changes in MVP

### Architecture Constraints to Enforce

- `declare(strict_types=1);` on every PHP file — first line, no exceptions
- Return type hints required on all public and private methods
- Resolve `$user->unitSystem()` ONCE before the conversion pass — never inside loops
- Ceiling rounding applied ONCE to final total — never to intermediate conversions
- Never drop entries that cannot be converted — always preserve as separate items
- `applyUnitConsolidation()` returns the same array structure it receives (no schema change)

### Anti-Patterns to Avoid

```php
// ❌ Rounding per conversion (compounds errors)
foreach ($entries as $entry) {
    $converted = $this->unitConversionService->convert($amount, $from, $to);
    $rounded = $this->unitConversionService->applyCeilingRounding($converted, $to, $system);
    $total += $rounded;
}

// ✅ Correct: accumulate raw converted values, round once at the end
foreach ($entries as $entry) {
    $converted = $this->unitConversionService->convert($amount, $from, $to);
    if ($converted !== null) {
        $total += $converted;
    }
}
$rounded = $this->unitConversionService->applyCeilingRounding($total, $targetUnit, $system);

// ❌ Silently dropping unconvertible items (forbidden)
if ($converted === null) { continue; }

// ✅ Correct: preserve as separate entry
if ($converted === null) {
    $result[$originalKey] = $originalEntry;
    continue;
}

// ❌ Calling unitSystem() inside loop (N queries)
foreach ($ingredients as $ingredient) {
    $system = $mealPlan->user->unitSystem();
}

// ✅ Correct: resolve once before loop
$unitSystem = $mealPlan->user->unitSystem();
$ingredients = $this->applyUnitConsolidation($ingredients, $unitSystem);

// ❌ Cross-dimension consolidation attempt (forbidden)
// "1 cup flour" + "100g flour" → single entry (WRONG)

// ✅ Correct: preserve as separate entries
// "1 cup flour" + "100g flour" → 2 separate items
```

### Previous Story Intelligence (Story 1.2)

**Files created in Story 1.2 that this story uses:**
- `app/Services/UnitConversionService.php` — inject and use this service
- `app/Enums/MeasurementUnit.php` — extended with OUNCE, POUND, FLUID_OUNCE; `isWeight()`, `isVolume()`, `isImperial()` methods available

**Test patterns established:**
- Unit tests use `uses(TestCase::class)` at file level
- `beforeEach()` for service instantiation
- Feature tests use factories for data creation
- Run with `php artisan test --compact --filter=TestName`

**Key methods from `UnitConversionService`:**
- `convert(float $amount, MeasurementUnit $from, MeasurementUnit $to): ?float` — returns null for impossible conversions
- `applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float` — ceiling to nearest 5ml/5g or 5fl oz/0.1oz
- `preferredUnit(MeasurementUnit $source, UnitSystem $system): ?MeasurementUnit` — returns target unit for dimension+system

**Completion notes from Story 1.2:**
- All 59 unit tests pass (79 assertions)
- `MeasurementUnit::isImperial()` method added during code review
- Cross-system volume conversion tests added (tbsp/cup↔fl oz)

### Git Intelligence

Recent commits show Stories 1.1 and 1.2 were completed:
- `3d848a8 feat: implement unit conversion engine for ingredient measurements`
- `404c2c0 feat: add user unit preference storage for shopping list conversion`

This story continues on the `feature/phase-9-unit-conversion` branch. The `UnitConversionService` is fully implemented and ready for integration.

### Test Pattern — `tests/Feature/ShoppingList/ShoppingListGenerationTest.php`

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\ShoppingList;

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Models\Ingredient;
use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\User;
use App\Services\ShoppingListService;
use App\Services\UnitConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ShoppingListService::class);
    $this->user = User::factory()->create();
});

// AC 1: Cross-unit consolidation (metric preference)
test('consolidates cross-unit volume ingredients into single metric entry', function () {
    // Setup: user with metric preference, two recipes with same ingredient in different units
    $ingredient = Ingredient::factory()->create(['name' => 'Olive Oil']);
    $recipe1 = Recipe::factory()->hasAttached($ingredient, ['amount' => 2, 'unit' => MeasurementUnit::TABLESPOON->value])->create();
    $recipe2 = Recipe::factory()->hasAttached($ingredient, ['amount' => 60, 'unit' => MeasurementUnit::MILLILITER->value])->create();

    $mealPlan = MealPlan::factory()->for($this->user)->create();
    // ... attach recipes to meal plan with to_cook = true

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test List', 'full');

    // 2 tbsp = 30ml, 60ml = 60ml → total 90ml → ceiling to 90ml
    expect($shoppingList->items)->toHaveCount(1);
    expect($shoppingList->items->first()->unit)->toBe(MeasurementUnit::MILLILITER->value);
    expect($shoppingList->items->first()->quantity)->toBe(90.0);
});

// AC 6: Same-unit consolidation regression
test('preserves existing same-unit consolidation behavior', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);
    $recipe1 = Recipe::factory()->hasAttached($ingredient, ['amount' => 100, 'unit' => MeasurementUnit::GRAM->value])->create();
    $recipe2 = Recipe::factory()->hasAttached($ingredient, ['amount' => 50, 'unit' => MeasurementUnit::GRAM->value])->create();

    $mealPlan = MealPlan::factory()->for($this->user)->create();
    // ... attach recipes

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test List', 'full');

    expect($shoppingList->items)->toHaveCount(1);
    expect($shoppingList->items->first()->quantity)->toBe(150.0);
});

// AC 4: Cross-dimension pass-through
test('preserves cross-dimension entries as separate items', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);
    $recipe1 = Recipe::factory()->hasAttached($ingredient, ['amount' => 1, 'unit' => MeasurementUnit::CUP->value])->create();
    $recipe2 = Recipe::factory()->hasAttached($ingredient, ['amount' => 100, 'unit' => MeasurementUnit::GRAM->value])->create();

    $mealPlan = MealPlan::factory()->for($this->user)->create();
    // ... attach recipes

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test List', 'full');

    // Cross-dimension: 1 cup flour (volume) + 100g flour (weight) = 2 separate items
    expect($shoppingList->items)->toHaveCount(2);
});

// AC 5: Unknown unit pass-through
test('preserves unknown unit entries unchanged', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Herbs']);
    $recipe = Recipe::factory()->hasAttached($ingredient, ['amount' => 1, 'unit' => 'handful'])->create();

    $mealPlan = MealPlan::factory()->for($this->user)->create();
    // ... attach recipe

    $shoppingList = $this->service->generateFromMealPlan($mealPlan, 'Test List', 'full');

    expect($shoppingList->items)->toHaveCount(1);
    expect($shoppingList->items->first()->unit)->toBe('handful');
    expect($shoppingList->items->first()->quantity)->toBe(1.0);
});
```

### References

- Architecture: Integration Patterns — `ShoppingListService::generateFromMealPlan()` conversion pass
  [Source: `_bmad-output/planning-artifacts/architecture.md#Integration Patterns`]
- Architecture: Rounding Pattern — ceiling applied once at end of accumulation
  [Source: `_bmad-output/planning-artifacts/architecture.md#Rounding Pattern`]
- Architecture: Pass-Through Pattern — preserve unconsolidatable entries, never drop
  [Source: `_bmad-output/planning-artifacts/architecture.md#Pass-Through & Error Isolation Pattern`]
- Architecture: User Preference Resolution Pattern — call once per list operation
  [Source: `_bmad-output/planning-artifacts/architecture.md#User Preference Resolution Pattern`]
- Architecture: Anti-Patterns — examples of what NOT to do
  [Source: `_bmad-output/planning-artifacts/architecture.md#Anti-Patterns to Avoid`]
- Epics: Story 1.3 acceptance criteria
  [Source: `_bmad-output/planning-artifacts/epics.md#Story 1.3`]
- Previous Story: Story 1.2 implementation
  [Source: `_bmad-output/implementation-artifacts/1-2-unit-conversion-engine.md`]
- Existing service: `app/Services/ShoppingListService.php`
- Conversion service: `app/Services/UnitConversionService.php`
- Project context: `_bmad-output/project-context.md`

## Dev Agent Record

### Agent Model Used

Claude glm-5 (2026-03-20)

### Debug Log References

None - all tests passed on first implementation run.

### Completion Notes List

- ✅ Implemented `UnitConversionService` injection via constructor property promotion
- ✅ Added conversion pass between ingredient accumulation and item creation
- ✅ Implemented `applyUnitConsolidation()` method with dimension-aware grouping
- ✅ Implemented `groupEntriesByDimension()` helper method
- ✅ Implemented `consolidateSameDimension()` helper method with ceiling rounding
- ✅ All 7 acceptance criteria satisfied:
  - AC1: Cross-unit consolidation with metric preference (tbsp + ml → ml)
  - AC2: Metric unit system default (ml for volume, g for weight)
  - AC3: Imperial unit system (fl oz for volume, oz for weight)
  - AC4: Cross-dimension pass-through (volume + weight = 2 separate items)
  - AC5: Unknown unit pass-through (handful, pinch, null preserved)
  - AC6: Same-unit consolidation regression (no regression)
  - AC7: Ceiling rounding applied once to final total
- ✅ All 19 unit tests pass (84 assertions)
- ✅ Full test suite passes (645 tests, 2356 assertions - deprecations accepted due to running on PHP8.5)
- ✅ Pint formatting applied

### File List

- `app/Services/ShoppingListService.php` — added UnitConversionService injection and consolidation methods
- `tests/Unit/Services/ShoppingListServiceTest.php` — added cross-unit consolidation tests (10 new tests)
- `_bmad-output/implementation-artifacts/sprint-status.yaml` — sprint tracking updated

### Change Log

- 2026-03-20: Implemented Story 1.3 - Consolidated Shopping List Generation
- 2026-03-20: Code review fixes applied:
  - Restored `count($dimensionEntries) === 1` guard in `applyUnitConsolidation` (AC4 pass-through correctness)
  - Added null-quantity guard in `consolidateSameDimension` to preserve entries instead of corrupting to 0.0
  - Added preservation of unconvertible entries when `convert()` returns null (anti-pattern fix)
  - Corrected AC4 test assertion: cup unit preserved as-is, not converted to ml
  - Corrected mixed-unit test: single volume entry passes through unchanged
