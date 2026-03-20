# Story 2.1: Unit Conversion at Shopping List Sync

Status: done

## Story

As a NutriPlan user who has already generated a shopping list,
I want newly added meals to automatically contribute their ingredients converted to my preferred unit system,
So that my shopping list stays accurate and consolidated even when I change my plan mid-week.

## Acceptance Criteria

1. **Same-unit increment (regression)** — Given an existing list item `300g chicken breast` and a new meal requires `200g chicken breast`, when the sync job runs, then the existing item's quantity is incremented to `500g` (same unit, no conversion needed — existing behaviour preserved).

2. **Cross-unit increment (metric)** — Given a metric-preference user with an existing list item `300g chicken breast` and a new meal requires `0.5 lb chicken breast`, when the sync job runs, then the `0.5 lb` is converted to grams, ceiling-rounded, and added to the existing `300g` entry — a single consolidated entry remains.

3. **Cross-unit increment (imperial)** — Given an imperial-preference user with an existing list item `10 fl oz olive oil` and a new meal requires `60ml olive oil`, when the sync job runs, then the `60ml` is converted to fl oz, ceiling-rounded, and added to the existing `10 fl oz` entry.

4. **Unconvertible unit — new separate item** — Given a new meal ingredient has an unconvertible unit (cross-dimension or non-standard), when the sync job runs, then a new separate list item is created using the ingredient's original unit — no error is produced and no existing item is modified.

5. **New ingredient with preferred unit** — Given a new meal ingredient that doesn't exist on the list at all and the user has metric preference, when the sync job runs, then a new list item is created with the ingredient amount converted to the user's preferred unit and ceiling-rounded.

6. **Shopping list owner's preference (FR8a)** — Given the shopping list belongs to User A but the meal was added by a session acting as User B, when the sync job runs, then User A's unit preference is used for all conversion decisions — not the requesting session's preference.

7. **Ceiling rounding per incoming ingredient** — Ceiling rounding is applied to each incoming converted amount before it is added to an existing item or used to create a new item.

## Tasks / Subtasks

- [x] Add `UnitConversionService` injection via `handle()` method type-hint (AC: all)
  - [x] Change `handle(): void` to `handle(UnitConversionService $conversionService): void`
  - [x] Add `use App\Services\UnitConversionService;` import
  - [x] Laravel service container resolves this automatically when job is dispatched via queue

- [x] Eager-load shopping list owner before ingredient loop (AC: 6)
  - [x] Add `$this->shoppingList->loadMissing('user');` before the ingredient loop
  - [x] Resolve unit system once: `$unitSystem = $this->shoppingList->user->unitSystem();`
  - [x] Never call `unitSystem()` inside the per-ingredient loop

- [x] Convert each incoming ingredient to preferred unit before lookup (AC: 2, 3, 4, 5)
  - [x] For each ingredient, attempt `$conversionService->preferredUnit($sourceUnit, $unitSystem)`
  - [x] If preferred unit found: `convert($pivot->amount, $sourceUnit, $targetUnit)` → apply `applyCeilingRounding()` → use `$targetUnit->value` for lookup key
  - [x] If conversion returns `null` (cross-dimension, unknown, null unit): fall back to `$pivot->unit` for lookup key and `$pivot->amount` as quantity (existing behaviour)
  - [x] Build lookup key as `"{$ingredient->id}:{$resolvedUnit}"` where `$resolvedUnit` is the converted unit value or original pivot unit

- [x] Update `item create` path to use converted amount and unit (AC: 5)
  - [x] When creating a new item (no match), use converted amount and converted unit (not original pivot values) if conversion succeeded

- [x] Update existing tests in `UpdateShoppingListJobTest.php` (regression safety)
  - [x] All `$job->handle()` calls become `$job->handle(new UnitConversionService())`
  - [x] `UnitConversionService` has no constructor arguments — safe to instantiate directly in tests
  - [x] Verify all existing tests still pass: `php artisan test --compact --filter=UpdateShoppingListJobTest`

- [x] Write new feature tests for unit conversion paths (AC: 2, 3, 4, 5, 6)
  - [x] Test: metric-preference user — incoming `lb` matches existing `g` item (increment after conversion)
  - [x] Test: imperial-preference user — incoming `ml` matches existing `fl oz` item (increment after conversion)
  - [x] Test: cross-dimension incoming ingredient creates new separate item (no error, no existing item modified)
  - [x] Test: new ingredient (not on list) converted to preferred unit on create
  - [x] Test: shopping list owner's preference used, not session user (FR8a)
  - [x] Run: `php artisan test --compact --filter=UpdateShoppingListJobTest`

- [x] Run Pint formatter: `vendor/bin/pint --dirty --format agent`

## Dev Notes

### Current `UpdateShoppingListJob::handle()` — Exact State

File: `app/Jobs/UpdateShoppingListJob.php`

Current signature (line 53): `public function handle(): void`

Current lookup key (line 93): `$lookupKey = "{$ingredient->id}:{$pivot->unit}";`

Existing items are keyed (line 82): `"{$item->ingredient_id}:{$item->unit}"` — this format must be preserved exactly for cross-unit matching to work.

The ingredient loop at line 85 iterates `$recipe->ingredients` and accesses pivot data via `$ingredient->pivot`.

**No user relationship is loaded on `$this->shoppingList` in the current implementation.** It must be added before any unit system resolution.

### `handle()` Signature Change — Exact Implementation

```php
public function handle(UnitConversionService $conversionService): void
{
    // Ensure relationships are loaded (defensive programming)
    $this->meal->loadMissing('mealPlanRecipe.recipe.ingredients');

    // Eager-load shopping list owner for preference resolution (FR8a)
    $this->shoppingList->loadMissing('user');

    // Get the recipe through the relationship chain
    $mealPlanRecipe = $this->meal->mealPlanRecipe;
    $recipe = $mealPlanRecipe?->recipe;

    if ($recipe === null) {
        Log::info('Shopping list update skipped - no recipe found', [...]);
        return;
    }

    $ingredients = $recipe->ingredients;
    $maxOrder = $this->shoppingList->items()->max('order') ?? 0;
    $existingItems = $this->shoppingList->items()
        ->get(['id', 'ingredient_id', 'unit', 'quantity'])
        ->keyBy(fn (ShoppingListItem $item) => "{$item->ingredient_id}:{$item->unit}");

    // Resolve user preference ONCE before the loop (never inside loop)
    $unitSystem = $this->shoppingList->user->unitSystem();

    foreach ($ingredients as $ingredient) {
        $pivot = $ingredient->pivot;

        if ($pivot->amount === null || $pivot->amount <= 0) {
            continue;
        }

        // Attempt unit conversion
        $resolvedUnit = $pivot->unit;
        $resolvedAmount = $pivot->amount;

        $sourceUnit = $pivot->unit !== null ? MeasurementUnit::tryFrom($pivot->unit) : null;

        if ($sourceUnit !== null) {
            $targetUnit = $conversionService->preferredUnit($sourceUnit, $unitSystem);

            if ($targetUnit !== null) {
                $converted = $conversionService->convert($pivot->amount, $sourceUnit, $targetUnit);

                if ($converted !== null) {
                    $resolvedAmount = $conversionService->applyCeilingRounding($converted, $targetUnit, $unitSystem);
                    $resolvedUnit = $targetUnit->value;
                }
            }
        }

        $lookupKey = "{$ingredient->id}:{$resolvedUnit}";
        $existingItem = $existingItems->get($lookupKey);

        if ($existingItem) {
            $existingItem->increment('quantity', $resolvedAmount);
            $existingItem->touch();
        } else {
            $this->shoppingList->items()->create([
                'name'          => $ingredient->name,
                'quantity'      => $resolvedAmount,
                'unit'          => $resolvedUnit,
                'category'      => null,
                'ingredient_id' => $ingredient->id,
                'is_custom'     => false,
                'order'         => ++$maxOrder,
            ]);
        }
    }

    $message = "List updated with new ingredients from {$recipe->title}";
    ShoppingListUpdated::dispatch($this->shoppingList->user_id, $message, $this->shoppingList->id);
}
```

Add to imports block at top of file:
```php
use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Services\UnitConversionService;
```

### CRITICAL: Existing Tests Must Be Updated

**All 11 existing tests in `tests/Feature/Jobs/UpdateShoppingListJobTest.php` call `$job->handle()` with no arguments.** After the signature change, PHP will error unless the argument is provided.

**Update every occurrence:**
```php
// Before:
$job->handle();

// After:
$job->handle(new UnitConversionService());
```

`UnitConversionService` is a pure service with no constructor dependencies — safe to instantiate directly in tests. Do NOT use `app(UnitConversionService::class)` in unit/feature tests where direct instantiation is cleaner.

**Tests to update (line references are approximate):**
- `it('increments quantity when ingredient exists with same unit')` — line ~73
- `it('creates new item when ingredient exists with different unit')` — line ~103
- `it('creates new shopping list item for new ingredient')` — line ~129
- `it('dispatches ShoppingListUpdated event on success')` — line ~152
- `it('handles recipe with no ingredients gracefully')` — line ~165
- `it('handles multiple ingredients correctly')` — line ~200
- `it('sets order for new items at end of list')` — line ~224
- `it('handles missing recipe relationship gracefully')` — line ~238 (inside closure)
- `it('skips ingredients with zero or null amount')` — line ~281
- `it('eager loads relationships to prevent N+1 queries')` — line ~305
- `it('has correct retry configuration')` — no `handle()` call, no change needed

### Lookup Key Format — Critical Matching Logic

Existing items are keyed by: `"{$item->ingredient_id}:{$item->unit}"`
where `$item->unit` is the raw string value stored in the database (e.g., `'g'`, `'ml'`, `'fl oz'`).

After conversion, the resolved unit comes from `$targetUnit->value` (e.g., `MeasurementUnit::GRAM->value === 'g'`).
These string values are identical, so converted keys match stored item keys correctly.

**Example flow (metric user, incoming `0.5 lb` chicken, existing `300g` chicken):**
- `sourceUnit` = `MeasurementUnit::POUND` (value `'lb'`)
- `targetUnit` = `MeasurementUnit::GRAM` (value `'g'`) via `preferredUnit(POUND, Metric)`
- `convert(0.5, POUND, GRAM)` → ~`226.8`
- `applyCeilingRounding(226.8, GRAM, Metric)` → `230` (ceiling to nearest 5g)
- `resolvedUnit` = `'g'`; lookup key = `"{chicken->id}:g"`
- Existing item keyed as `"{chicken->id}:g"` → **match found → increment by 230**

**Example flow (fallback, cross-dimension or null unit):**
- `resolvedUnit` stays as `$pivot->unit` (original string)
- `resolvedAmount` stays as `$pivot->amount` (original float)
- Lookup key built from original values → creates new item if no match (existing behaviour)

### Service Injection Pattern — Job vs Service (Architecture Rule)

**`UpdateShoppingListJob` (queued job):** Use `handle()` method injection — NOT constructor injection.
Laravel serializes constructor arguments when dispatching to queue. Services are not serializable and will cause `RuntimeException: Unable to serialize`.

```php
// CORRECT for queued jobs:
public function handle(UnitConversionService $conversionService): void { ... }

// FORBIDDEN in queued jobs:
public function __construct(..., UnitConversionService $conversionService) {}
```

This is different from `ShoppingListService` (regular service, not a job) which uses constructor injection.

### User Preference — Owner Rule (FR8a)

The shopping list's owner (`$this->shoppingList->user`) determines the unit preference — NOT the currently authenticated user or the user who triggered the sync.

This is guaranteed by: `$this->shoppingList->loadMissing('user')` followed by `$this->shoppingList->user->unitSystem()`.

Never use `Auth::user()->unitSystem()` or any session-based user — the job runs async and has no session context.

### Test Pattern for New Conversion Tests

Add to `tests/Feature/Jobs/UpdateShoppingListJobTest.php`, following the existing `beforeEach` structure:

```php
use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Services\UnitConversionService;

it('converts incoming lb ingredient to g and increments matching existing item (metric user)', function () {
    // Set user to metric (default, no change needed — User::unitSystem() defaults to metric)
    $ingredient = Ingredient::factory()->create(['name' => 'Chicken Breast']);

    // Existing item: 300g
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id'    => $ingredient->id,
        'name'             => 'Chicken Breast',
        'quantity'         => 300.0,
        'unit'             => MeasurementUnit::GRAM->value,
        'is_custom'        => false,
    ]);

    // New recipe ingredient: 0.5 lb
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 0.5,
        'unit'   => MeasurementUnit::POUND->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 0.5 lb = 226.796g → ceiling to nearest 5g = 230g → 300 + 230 = 530
    expect($this->shoppingList->items()->count())->toBe(1);
    expect((float) $existingItem->quantity)->toBe(530.0);
    expect($existingItem->unit)->toBe(MeasurementUnit::GRAM->value);
});

it('converts incoming ml ingredient to fl oz and increments matching existing item (imperial user)', function () {
    // Set user to imperial preference
    $this->user->setSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Imperial->value);

    $ingredient = Ingredient::factory()->create(['name' => 'Olive Oil']);

    // Existing item: 10 fl oz
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id'    => $ingredient->id,
        'name'             => 'Olive Oil',
        'quantity'         => 10.0,
        'unit'             => MeasurementUnit::FLUID_OUNCE->value,
        'is_custom'        => false,
    ]);

    // New recipe ingredient: 60ml
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 60.0,
        'unit'   => MeasurementUnit::MILLILITER->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 60ml ÷ 29.5735 = ~2.03 fl oz → ceiling to nearest 5 fl oz = 5.0 fl oz → 10 + 5 = 15
    expect($this->shoppingList->items()->count())->toBe(1);
    expect((float) $existingItem->quantity)->toBe(15.0);
    expect($existingItem->unit)->toBe(MeasurementUnit::FLUID_OUNCE->value);
});

it('creates new item with preferred unit when ingredient not on list (metric user)', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Milk']);

    // New recipe ingredient: 2 cups (not on list yet)
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 2.0,
        'unit'   => MeasurementUnit::CUP->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    expect($this->shoppingList->items()->count())->toBe(1);
    $newItem = $this->shoppingList->items()->first();
    // 2 cups = 480ml → already at 5ml boundary → 480ml
    expect($newItem->unit)->toBe(MeasurementUnit::MILLILITER->value);
    expect((float) $newItem->quantity)->toBe(480.0);
    expect($newItem->ingredient_id)->toBe($ingredient->id);
});

it('creates new separate item for cross-dimension ingredient without modifying existing (pass-through)', function () {
    $ingredient = Ingredient::factory()->create(['name' => 'Flour']);

    // Existing item: 200ml flour (volume)
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id'    => $ingredient->id,
        'name'             => 'Flour',
        'quantity'         => 200.0,
        'unit'             => MeasurementUnit::MILLILITER->value,
        'is_custom'        => false,
    ]);

    // New recipe ingredient: 100g flour (weight — cross-dimension)
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 100.0,
        'unit'   => MeasurementUnit::GRAM->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    // Existing item unchanged, new separate item created
    $existingItem->refresh();
    expect($this->shoppingList->items()->count())->toBe(2);
    expect((float) $existingItem->quantity)->toBe(200.0); // not modified
});

it('uses shopping list owner preference, not session user preference (FR8a)', function () {
    // Shopping list owner (User A) has imperial preference
    $this->user->setSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Imperial->value);

    // A second user (User B) exists but is irrelevant
    $otherUser = User::factory()->create(); // metric by default

    $ingredient = Ingredient::factory()->create(['name' => 'Butter']);

    // Existing item: 5 oz (imperial, because list owner is imperial)
    $existingItem = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id'    => $ingredient->id,
        'name'             => 'Butter',
        'quantity'         => 5.0,
        'unit'             => MeasurementUnit::OUNCE->value,
        'is_custom'        => false,
    ]);

    // New recipe ingredient: 100g
    $this->recipe->ingredients()->attach($ingredient->id, [
        'amount' => 100.0,
        'unit'   => MeasurementUnit::GRAM->value,
    ]);

    $job = new UpdateShoppingListJob($this->shoppingList, $this->mealAssignment);
    $job->handle(new UnitConversionService());

    $existingItem->refresh();
    // 100g → oz (imperial preference from list owner) → ~3.53 oz → ceiling 0.1 oz = 3.6 oz → 5 + 3.6 = 8.6
    expect($this->shoppingList->items()->count())->toBe(1);
    expect($existingItem->unit)->toBe(MeasurementUnit::OUNCE->value);
    // Quantity should be > 5 (conversion succeeded and merged)
    expect((float) $existingItem->quantity)->toBeGreaterThan(5.0);
});
```

### Architecture Constraints to Enforce

- `declare(strict_types=1);` first line — no exceptions
- Return type hint on `handle()`: `void`
- `UnitConversionService` injected via `handle()` type-hint only (NOT constructor — serialization will break queue)
- `$this->shoppingList->user->unitSystem()` called ONCE before loop — never inside loop
- Ceiling rounding applied to each incoming converted ingredient — not accumulated across ingredients
- `convert()` returning `null` means fall back to original unit/amount (never drop, never error)
- Preserve all existing behaviour for unconvertible units (cross-dimension, null, non-standard strings)

### Anti-Patterns to Avoid

```php
// FORBIDDEN: constructor injection in queued job (breaks serialization)
public function __construct(
    public readonly ShoppingList $shoppingList,
    public readonly MealAssignment $meal,
    private UnitConversionService $conversionService  // <-- BREAKS QUEUE
) {}

// FORBIDDEN: calling unitSystem() inside ingredient loop (N queries)
foreach ($ingredients as $ingredient) {
    $unitSystem = $this->shoppingList->user->unitSystem(); // <-- N QUERIES
}

// FORBIDDEN: dropping item when conversion fails (destroys data)
if ($converted === null) {
    continue; // <-- DROPS THE INGREDIENT SILENTLY
}

// FORBIDDEN: using Auth::user() in async job (no session context in queue)
$unitSystem = Auth::user()->unitSystem(); // <-- WRONG USER

// FORBIDDEN: rounding after each increment (wrong semantics)
$existingItem->increment('quantity', $roundedAmount); // ok IF rounding is already applied
// but do NOT double-round: apply rounding once to incoming $converted before lookup
```

### Previous Story Intelligence (Story 1.3)

**Files modified in Story 1.3 (completed):**
- `app/Services/ShoppingListService.php` — already has `UnitConversionService` injected via constructor; has `applyUnitConsolidation()`, `groupEntriesByDimension()`, `consolidateSameDimension()` helper methods — do NOT duplicate this logic in the job
- `tests/Unit/Services/ShoppingListServiceTest.php` — has 10 cross-unit consolidation tests passing

**Code review corrections from Story 1.3 to avoid repeating:**
- Always guard null quantities before conversion (Story 1.3 needed a null-quantity guard)
- Always preserve entries when `convert()` returns null — do not continue/skip
- `count($dimensionEntries) === 1` guard matters — single-unit groups must pass through unchanged

**Test patterns from Story 1.3:**
- Feature tests use `uses(TestCase::class, RefreshDatabase::class);`
- `beforeEach()` for shared setup
- Direct service/job instantiation (not `app()`) in tests: `new UnitConversionService()`
- Run: `php artisan test --compact --filter=UpdateShoppingListJobTest`

### Key Methods from `UnitConversionService` (completed in Story 1.2)

```php
// Returns null if cross-dimension, unknown unit, or null unit
public function convert(float $amount, MeasurementUnit $from, MeasurementUnit $to): ?float

// Ceiling rounding: nearest 5ml/5g (metric), nearest 5fl oz/0.1oz (imperial)
public function applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float

// Returns preferred unit for source unit's dimension + system; null for dimensionless (PIECE, PINCH, CLOVE)
public function preferredUnit(MeasurementUnit $source, UnitSystem $system): ?MeasurementUnit

// Constant for settings key — use this, never bare string 'unit_system'
public const string UNIT_SYSTEM_SETTING = 'unit_system';
```

**Preferred unit mappings:**
- Volume + Metric → `MeasurementUnit::MILLILITER`
- Volume + Imperial → `MeasurementUnit::FLUID_OUNCE`
- Weight + Metric → `MeasurementUnit::GRAM`
- Weight + Imperial → `MeasurementUnit::OUNCE`

### Git Intelligence

Recent commits confirm Epic 1 is fully complete:
- `03b1ce9` — consolidated shopping list story + test coverage (Story 1.3)
- `3d848a8` — unit conversion engine (Story 1.2)
- `404c2c0` — user unit preference storage (Story 1.1)

All Epic 1 components are available: `UnitConversionService`, `UnitSystem` enum, `MeasurementUnit` imperial cases, `User::unitSystem()`. Epic 2 Story 1 is the only remaining story.

### Project Structure Notes

**File to modify:**
- `app/Jobs/UpdateShoppingListJob.php` — add `UnitConversionService` `handle()` injection, `loadMissing('user')`, conversion logic before lookup key
- `tests/Feature/Jobs/UpdateShoppingListJobTest.php` — update all 10 `$job->handle()` calls to `$job->handle(new UnitConversionService())`; add 5 new conversion tests

**Files NOT touched:**
- `app/Services/UnitConversionService.php` — complete, no changes
- `app/Services/ShoppingListService.php` — complete, no changes
- `app/Enums/MeasurementUnit.php` — complete, no changes
- `app/Enums/UnitSystem.php` — complete, no changes
- `app/Models/User.php` — complete, no changes
- All frontend files — no frontend changes in MVP

### References

- Epics: Story 2.1 acceptance criteria and technical scope [Source: `_bmad-output/planning-artifacts/epics.md#Story 2.1`]
- Architecture: Integration Patterns — `UpdateShoppingListJob::handle()` injection and conversion flow [Source: `_bmad-output/planning-artifacts/architecture.md#Integration Patterns`]
- Architecture: Lookup Key Pattern — build key from converted unit value [Source: `_bmad-output/planning-artifacts/architecture.md#Lookup Key Pattern`]
- Architecture: Coherence Validation — job handle() injection correction [Source: `_bmad-output/planning-artifacts/architecture.md#Architecture Validation Results`]
- Architecture: User Preference Resolution Pattern — once per list operation [Source: `_bmad-output/planning-artifacts/architecture.md#User Preference Resolution Pattern`]
- Architecture: Pass-Through Pattern — null → preserve original [Source: `_bmad-output/planning-artifacts/architecture.md#Pass-Through & Error Isolation Pattern`]
- Architecture: Anti-Patterns [Source: `_bmad-output/planning-artifacts/architecture.md#Anti-Patterns to Avoid`]
- Previous story: Story 1.3 learnings [Source: `_bmad-output/implementation-artifacts/1-3-consolidated-shopping-list-generation.md`]
- Existing job: `app/Jobs/UpdateShoppingListJob.php`
- Existing job tests: `tests/Feature/Jobs/UpdateShoppingListJobTest.php`
- Project context: `_bmad-output/project-context.md`

## Dev Agent Record

### Agent Model Used

Claude Sonnet 4.6 (2026-03-20)

### Debug Log References

N/A — implementation proceeded cleanly without blockers.

### Completion Notes List

- Implemented `UnitConversionService` injection via `handle()` method type-hint (not constructor — serialization safety for queued jobs).
- Added `$this->shoppingList->loadMissing('user')` before the ingredient loop; `unitSystem()` resolved once before the loop (FR8a, N+1 prevention).
- Conversion logic: for each ingredient, attempts `preferredUnit()` → `convert()` → `applyCeilingRounding()`. Falls back to original unit/amount on any null result (cross-dimension, dimensionless, unknown unit).
- All 12 existing `$job->handle()` calls updated to `$job->handle(new UnitConversionService())`.
- 8 new feature tests added covering ACs 2, 3, 4, 5, 6 plus dimensionless-unit and null-unit pass-through. `setSetting` corrected to `addSetting` (matching the `HasSettings` trait API).
- Pint removed unused `UnitSystem` import from job file (not directly referenced in job code).
- All 19 tests in `UpdateShoppingListJobTest` pass; full suite of 652 tests passes with no regressions.

#### Code Review Fixes (2026-03-20)

- **H1 fix:** Added `$targetUnit !== $sourceUnit` guard to skip conversion+rounding when source and target units are identical, preserving existing behavior per AC1.
- **M1 fix:** Strengthened FR8a test assertion from `toBeGreaterThan(5.0)` to exact value `toBe(8.6)`.
- **M2 fix:** Added test for dimensionless unit (PIECE) pass-through without conversion or rounding.
- **M3 fix:** Added test for null-unit ingredient pass-through unchanged.

### File List

- `app/Jobs/UpdateShoppingListJob.php`
- `tests/Feature/Jobs/UpdateShoppingListJobTest.php`
