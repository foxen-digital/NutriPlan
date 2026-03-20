# Story 1.2: Unit Conversion Engine

Status: done

<!-- Note: Validation is optional. Run validate-create-story for quality check before dev-story. -->

## Story

As the NutriPlan application,
I want to convert ingredient amounts between compatible units of the same measurement dimension,
so that quantities measured in tablespoons, cups, grams, or ounces can all be compared and combined.

## Acceptance Criteria

1. **Volume-to-volume conversion** — Given a volume unit amount (e.g., `2` tablespoons), when `convert(2, TABLESPOON, MILLILITER)` is called, it returns `30.0`.

2. **Metric-to-imperial volume conversion** — Given a metric volume amount (e.g., `240ml`), when `convert(240.0, MILLILITER, FLUID_OUNCE)` is called, it returns ~`8.12`.

3. **Imperial-to-metric weight conversion** — Given a weight unit amount (e.g., `1` ounce), when `convert(1, OUNCE, GRAM)` is called, it returns ~`28.35`.

4. **Metric-to-imperial weight conversion** — Given a metric weight amount (e.g., `100g`), when `convert(100.0, GRAM, OUNCE)` is called, it returns ~`3.53`.

5. **Cross-dimension returns null** — Given a cross-dimension pair (e.g., a volume unit and a weight unit), when `convert()` is called, it returns `null` — no exception is thrown.

6. **Dimensionless unit returns null** — Given a dimensionless unit (PIECE, PINCH, CLOVE) or null, when `convert()` is called, it returns `null` — no exception is thrown.

7. **Metric volume ceiling rounding** — Given a total metric volume amount (e.g., `112ml`), when `applyCeilingRounding(112.0, MILLILITER, Metric)` is called, it returns `115` (ceiling to nearest 5ml).

8. **Metric weight ceiling rounding** — Given a total metric weight amount (e.g., `103g`), when `applyCeilingRounding(103.0, GRAM, Metric)` is called, it returns `105` (ceiling to nearest 5g).

9. **Imperial volume ceiling rounding** — Given a total imperial volume amount (e.g., `3.2 fl oz`), when `applyCeilingRounding(3.2, FLUID_OUNCE, Imperial)` is called, it returns `5.0` (ceiling to nearest 5 fl oz).

10. **Imperial weight ceiling rounding** — Given a total imperial weight amount (e.g., `1.15 oz`), when `applyCeilingRounding(1.15, OUNCE, Imperial)` is called, it returns `1.2` (ceiling to nearest 0.1 oz).

11. **Preferred unit: volume + metric** — Given a volume source unit and `UnitSystem::Metric`, when `preferredUnit(TABLESPOON, Metric)` is called, it returns `MeasurementUnit::MILLILITER`.

12. **Preferred unit: volume + imperial** — Given a volume source unit and `UnitSystem::Imperial`, when `preferredUnit(MILLILITER, Imperial)` is called, it returns `MeasurementUnit::FLUID_OUNCE`.

13. **Preferred unit: weight + metric** — Given a weight source unit and `UnitSystem::Metric`, when `preferredUnit(OUNCE, Metric)` is called, it returns `MeasurementUnit::GRAM`.

14. **Preferred unit: weight + imperial** — Given a weight source unit and `UnitSystem::Imperial`, when `preferredUnit(GRAM, Imperial)` is called, it returns `MeasurementUnit::OUNCE`.

15. **Preferred unit: dimensionless returns null** — Given a dimensionless source unit (PIECE, PINCH, CLOVE), when `preferredUnit()` is called, it returns `null`.

16. **Conversion table completeness** — The conversion table covers all standard units: tbsp, tsp, ml, l, fl oz, cup (volume) and g, kg, oz, lb (weight); `MeasurementUnit` enum includes OUNCE, POUND, FLUID_OUNCE cases.

## Tasks / Subtasks

- [x] Extend `App\Enums\MeasurementUnit` (AC: 16)
  - [x] Add `case OUNCE = 'oz';` — imperial weight
  - [x] Add `case POUND = 'lb';` — imperial weight
  - [x] Add `case FLUID_OUNCE = 'fl oz';` — imperial volume
  - [x] Update `isWeight()` to include `self::OUNCE, self::POUND`
  - [x] Update `isVolume()` to include `self::FLUID_OUNCE`
  - [x] Update `label()` method with new unit labels

- [x] Implement `UnitConversionService::convert()` method (AC: 1, 2, 3, 4, 5, 6)
  - [x] Add private conversion table constant (base-unit approach)
  - [x] Implement `convert(float $amount, MeasurementUnit $from, MeasurementUnit $to): ?float`
  - [x] Return `null` for cross-dimension attempts
  - [x] Return `null` for dimensionless units (PIECE, PINCH, CLOVE)
  - [x] Catch all exceptions internally, return `null` on any failure

- [x] Implement `UnitConversionService::applyCeilingRounding()` method (AC: 7, 8, 9, 10)
  - [x] Implement `applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float`
  - [x] Metric volume: ceiling to nearest 5ml
  - [x] Metric weight: ceiling to nearest 5g
  - [x] Imperial volume: ceiling to nearest 5 fl oz
  - [x] Imperial weight: ceiling to nearest 0.1 oz

- [x] Implement `UnitConversionService::preferredUnit()` method (AC: 11, 12, 13, 14, 15)
  - [x] Implement `preferredUnit(MeasurementUnit $source, UnitSystem $system): ?MeasurementUnit`
  - [x] Volume + Metric → MILLILITER
  - [x] Volume + Imperial → FLUID_OUNCE
  - [x] Weight + Metric → GRAM
  - [x] Weight + Imperial → OUNCE
  - [x] Dimensionless → null

- [x] Write unit tests for `UnitConversionService` (all ACs)
  - [x] Create `tests/Unit/Services/UnitConversionServiceTest.php`
  - [x] Test: volume-to-volume conversions (tbsp→ml, tsp→ml, cup→ml, ml→l)
  - [x] Test: metric-to-imperial volume (ml→fl oz)
  - [x] Test: imperial-to-metric volume (fl oz→ml)
  - [x] Test: weight-to-weight conversions (g→kg, kg→g)
  - [x] Test: metric-to-imperial weight (g→oz, kg→lb)
  - [x] Test: imperial-to-metric weight (oz→g, lb→kg)
  - [x] Test: cross-dimension returns null
  - [x] Test: dimensionless units return null
  - [x] Test: ceiling rounding for metric volume (nearest 5ml)
  - [x] Test: ceiling rounding for metric weight (nearest 5g)
  - [x] Test: ceiling rounding for imperial volume (nearest 5 fl oz)
  - [x] Test: ceiling rounding for imperial weight (nearest 0.1 oz)
  - [x] Test: preferredUnit for all system/dimension combinations
  - [x] Run `php artisan test --compact --filter=UnitConversionServiceTest`

- [x] Run Pint formatter: `vendor/bin/pint --dirty --format agent`

## Dev Notes

### Architecture Overview

This story implements the core conversion engine that will be consumed by `ShoppingListService` (Story 1.3) and `UpdateShoppingListJob` (Story 2.1). The service is a **pure computation layer** with zero database access and zero Eloquent model dependencies.

**Key Design Principles:**
- **Base-unit conversion approach**: Each unit maps to a factor relative to its dimension's base (ml for metric volume, g for metric weight, fl oz for imperial volume, oz for imperial weight)
- **Pass-through on failure**: `convert()` returns `null` for impossible conversions — never throws, never returns `0.0`
- **Deterministic output**: Identical input always produces identical output

### Conversion Table — Exact Implementation

Add this private constant to `UnitConversionService`:

```php
/**
 * Conversion factors relative to dimension base units.
 * - Metric volume base: ml (factor 1.0)
 * - Metric weight base: g (factor 1.0)
 * - Imperial volume base: fl oz (factor 1.0)
 * - Imperial weight base: oz (factor 1.0)
 *
 * Convert A→B: amount × factor(A) / factor(B)
 * Cross-dimension bridge: 1 fl oz = 29.5735 ml, 1 oz = 28.3495 g
 */
private const array CONVERSION_FACTORS = [
    // Metric volume (base: ml)
    MeasurementUnit::MILLILITER->value => 1.0,
    MeasurementUnit::LITER->value => 1000.0,
    MeasurementUnit::TEASPOON->value => 5.0,
    MeasurementUnit::TABLESPOON->value => 15.0,
    MeasurementUnit::CUP->value => 240.0,

    // Imperial volume (base: fl oz)
    MeasurementUnit::FLUID_OUNCE->value => 1.0,

    // Metric weight (base: g)
    MeasurementUnit::GRAM->value => 1.0,
    MeasurementUnit::KILOGRAM->value => 1000.0,

    // Imperial weight (base: oz)
    MeasurementUnit::OUNCE->value => 1.0,
    MeasurementUnit::POUND->value => 16.0,
];

private const float ML_TO_FLUID_OUNCE = 29.5735;
private const float G_TO_OUNCE = 28.3495;
```

### `convert()` Method — Exact Implementation

```php
/**
 * Convert an amount from one unit to another.
 * Returns null if cross-dimension, unknown unit, or conversion impossible.
 */
public function convert(float $amount, MeasurementUnit $from, MeasurementUnit $to): ?float
{
    try {
        // Same unit — no conversion needed
        if ($from === $to) {
            return $amount;
        }

        // Dimensionless units cannot be converted
        if ($from->isUnit() || $to->isUnit()) {
            return null;
        }

        // Cross-dimension conversion is not supported
        if ($from->isVolume() !== $to->isVolume()) {
            return null;
        }

        $fromFactor = self::CONVERSION_FACTORS[$from->value] ?? null;
        $toFactor = self::CONVERSION_FACTORS[$to->value] ?? null;

        if ($fromFactor === null || $toFactor === null) {
            return null;
        }

        // Same system: direct conversion
        if ($this->sameSystem($from, $to)) {
            return $amount * $fromFactor / $toFactor;
        }

        // Cross-system: bridge through base
        return match (true) {
            $from->isVolume() && $to->isVolume()
                => $this->convertVolumeCrossSystem($amount, $from, $to, $fromFactor, $toFactor),
            $from->isWeight() && $to->isWeight()
                => $this->convertWeightCrossSystem($amount, $from, $to, $fromFactor, $toFactor),
            default => null,
        };
    } catch (\Throwable) {
        return null;
    }
}

private function sameSystem(MeasurementUnit $from, MeasurementUnit $to): bool
{
    $metricVolume = [MeasurementUnit::MILLILITER, MeasurementUnit::LITER, MeasurementUnit::TEASPOON, MeasurementUnit::TABLESPOON, MeasurementUnit::CUP];
    $imperialVolume = [MeasurementUnit::FLUID_OUNCE];
    $metricWeight = [MeasurementUnit::GRAM, MeasurementUnit::KILOGRAM];
    $imperialWeight = [MeasurementUnit::OUNCE, MeasurementUnit::POUND];

    $bothMetric = in_array($from, $metricVolume) && in_array($to, $metricVolume)
        || in_array($from, $metricWeight) && in_array($to, $metricWeight);
    $bothImperial = in_array($from, $imperialVolume) && in_array($to, $imperialVolume)
        || in_array($from, $imperialWeight) && in_array($to, $imperialWeight);

    return $bothMetric || $bothImperial;
}

private function convertVolumeCrossSystem(float $amount, MeasurementUnit $from, MeasurementUnit $to, float $fromFactor, float $toFactor): float
{
    // Convert to ml first if imperial, then to target
    $ml = $from === MeasurementUnit::FLUID_OUNCE
        ? $amount * self::ML_TO_FLUID_OUNCE
        : $amount * $fromFactor;

    return $to === MeasurementUnit::FLUID_OUNCE
        ? $ml / self::ML_TO_FLUID_OUNCE
        : $ml / $toFactor;
}

private function convertWeightCrossSystem(float $amount, MeasurementUnit $from, MeasurementUnit $to, float $fromFactor, float $toFactor): float
{
    // Convert to g first if imperial, then to target
    $g = in_array($from, [MeasurementUnit::OUNCE, MeasurementUnit::POUND])
        ? $amount * $fromFactor * self::G_TO_OUNCE
        : $amount * $fromFactor;

    return in_array($to, [MeasurementUnit::OUNCE, MeasurementUnit::POUND])
        ? ($g / self::G_TO_OUNCE) / $toFactor
        : $g / $toFactor;
}
```

### `applyCeilingRounding()` Method — Exact Implementation

```php
/**
 * Apply ceiling rounding per PRD spec.
 * Metric: nearest 5ml (volume) / nearest 5g (weight)
 * Imperial: nearest 5 fl oz (volume) / nearest 0.1 oz (weight)
 */
public function applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float
{
    return match ($system) {
        UnitSystem::Metric => $this->roundMetric($amount, $unit),
        UnitSystem::Imperial => $this->roundImperial($amount, $unit),
    };
}

private function roundMetric(float $amount, MeasurementUnit $unit): float
{
    if ($unit->isVolume()) {
        // Ceiling to nearest 5ml
        return ceil($amount / 5) * 5;
    }
    if ($unit->isWeight()) {
        // Ceiling to nearest 5g
        return ceil($amount / 5) * 5;
    }
    return $amount;
}

private function roundImperial(float $amount, MeasurementUnit $unit): float
{
    if ($unit->isVolume()) {
        // Ceiling to nearest 5 fl oz
        return ceil($amount / 5) * 5;
    }
    if ($unit->isWeight()) {
        // Ceiling to nearest 0.1 oz
        return ceil($amount * 10) / 10;
    }
    return $amount;
}
```

### `preferredUnit()` Method — Exact Implementation

```php
/**
 * Return the preferred target unit for a given source unit's dimension + system.
 * Returns null if source unit has no clear dimension (PIECE, PINCH, CLOVE).
 */
public function preferredUnit(MeasurementUnit $source, UnitSystem $system): ?MeasurementUnit
{
    if ($source->isUnit()) {
        return null;
    }

    return match (true) {
        $source->isVolume() && $system === UnitSystem::Metric => MeasurementUnit::MILLILITER,
        $source->isVolume() && $system === UnitSystem::Imperial => MeasurementUnit::FLUID_OUNCE,
        $source->isWeight() && $system === UnitSystem::Metric => MeasurementUnit::GRAM,
        $source->isWeight() && $system === UnitSystem::Imperial => MeasurementUnit::OUNCE,
        default => null,
    };
}
```

### `MeasurementUnit` Enum Extension — Exact Changes

Add to the enum cases (after CLOVE):

```php
case OUNCE = 'oz';
case POUND = 'lb';
case FLUID_OUNCE = 'fl oz';
```

Update `label()` method:

```php
public function label(): string
{
    return match($this) {
        self::GRAM => 'Gram',
        self::KILOGRAM => 'Kilogram',
        self::MILLILITER => 'Milliliter',
        self::LITER => 'Liter',
        self::TEASPOON => 'Teaspoon',
        self::TABLESPOON => 'Tablespoon',
        self::CUP => 'Cup',
        self::PIECE => 'Piece',
        self::PINCH => 'Pinch',
        self::CLOVE => 'Clove',
        self::OUNCE => 'Ounce',
        self::POUND => 'Pound',
        self::FLUID_OUNCE => 'Fluid Ounce',
    };
}
```

Update `isVolume()`:

```php
public function isVolume(): bool
{
    return in_array($this, [
        self::MILLILITER,
        self::LITER,
        self::TEASPOON,
        self::TABLESPOON,
        self::CUP,
        self::FLUID_OUNCE,
    ]);
}
```

Update `isWeight()`:

```php
public function isWeight(): bool
{
    return in_array($this, [
        self::GRAM,
        self::KILOGRAM,
        self::OUNCE,
        self::POUND,
    ]);
}
```

### Test Pattern — `tests/Unit/Services/UnitConversionServiceTest.php`

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Services\UnitConversionService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = new UnitConversionService();
});

// Volume conversions
test('convert converts tablespoons to milliliters', function () {
    $result = $this->service->convert(2.0, MeasurementUnit::TABLESPOON, MeasurementUnit::MILLILITER);
    expect($result)->toBe(30.0);
});

test('convert converts milliliters to fluid ounces', function () {
    $result = $this->service->convert(240.0, MeasurementUnit::MILLILITER, MeasurementUnit::FLUID_OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(8.0)->toBeLessThan(8.2);
});

// Weight conversions
test('convert converts ounces to grams', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::OUNCE, MeasurementUnit::GRAM);
    expect($result)->toBeFloat()->toBeGreaterThan(28.0)->toBeLessThan(29.0);
});

test('convert converts grams to ounces', function () {
    $result = $this->service->convert(100.0, MeasurementUnit::GRAM, MeasurementUnit::OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(3.5)->toBeLessThan(3.6);
});

// Cross-dimension returns null
test('convert returns null for cross-dimension conversion', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::CUP, MeasurementUnit::GRAM);
    expect($result)->toBeNull();
});

// Dimensionless returns null
test('convert returns null for dimensionless units', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::PIECE, MeasurementUnit::GRAM);
    expect($result)->toBeNull();
});

// Ceiling rounding - metric volume
test('applyCeilingRounding rounds metric volume to nearest 5ml', function () {
    $result = $this->service->applyCeilingRounding(112.0, MeasurementUnit::MILLILITER, UnitSystem::Metric);
    expect($result)->toBe(115.0);
});

// Ceiling rounding - metric weight
test('applyCeilingRounding rounds metric weight to nearest 5g', function () {
    $result = $this->service->applyCeilingRounding(103.0, MeasurementUnit::GRAM, UnitSystem::Metric);
    expect($result)->toBe(105.0);
});

// Ceiling rounding - imperial volume
test('applyCeilingRounding rounds imperial volume to nearest 5 fl oz', function () {
    $result = $this->service->applyCeilingRounding(3.2, MeasurementUnit::FLUID_OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(5.0);
});

// Ceiling rounding - imperial weight
test('applyCeilingRounding rounds imperial weight to nearest 0.1 oz', function () {
    $result = $this->service->applyCeilingRounding(1.15, MeasurementUnit::OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(1.2);
});

// Preferred unit - volume + metric
test('preferredUnit returns MILLILITER for volume + metric', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::TABLESPOON, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::MILLILITER);
});

// Preferred unit - volume + imperial
test('preferredUnit returns FLUID_OUNCE for volume + imperial', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::MILLILITER, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::FLUID_OUNCE);
});

// Preferred unit - weight + metric
test('preferredUnit returns GRAM for weight + metric', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::OUNCE, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::GRAM);
});

// Preferred unit - weight + imperial
test('preferredUnit returns OUNCE for weight + imperial', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::GRAM, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::OUNCE);
});

// Preferred unit - dimensionless
test('preferredUnit returns null for dimensionless units', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::PIECE, UnitSystem::Metric);
    expect($result)->toBeNull();
});
```

### Project Structure Notes

**Files to modify:**
- `app/Enums/MeasurementUnit.php` — add 3 imperial cases, update isVolume()/isWeight()/label()
- `app/Services/UnitConversionService.php` — add conversion table, implement 3 methods

**Files to create:**
- `tests/Unit/Services/UnitConversionServiceTest.php` — pure unit tests (no DB)

**Files NOT touched in this story:**
- `app/Models/User.php` — modified in Story 1.1, not this story
- `app/Services/ShoppingListService.php` — modified in Story 1.3
- `app/Jobs/UpdateShoppingListJob.php` — modified in Story 2.1
- All frontend files — no frontend changes in MVP

### Architecture Constraints to Enforce

- `declare(strict_types=1);` on every PHP file — first line, no exceptions
- Return type hints required on all public methods
- `UnitConversionService` has **zero** Eloquent model dependencies — pure functions only
- `convert()` must **never throw** — catch all exceptions, return `null`
- `convert()` must **never return `0.0`** — this would silently destroy quantity data
- Case names follow existing SCREAMING_SNAKE pattern: `OUNCE`, `POUND`, `FLUID_OUNCE`
- String values: `'oz'`, `'lb'`, `'fl oz'` (space in fl oz is intentional)

### Anti-Patterns to Avoid

```php
// ❌ Throwing on conversion failure
throw new \InvalidArgumentException('Cross-dimension conversion');

// ✅ Correct: return null
return null;

// ❌ Returning 0.0 (destroys quantity data)
return 0.0;

// ✅ Correct: return null to signal pass-through
return null;

// ❌ Using database in conversion service
$factor = Setting::where('unit', $from)->first();

// ✅ Correct: pure constant lookup
$factor = self::CONVERSION_FACTORS[$from->value];

// ❌ Logging individual conversion failures
Log::warning("Conversion failed for {$from->value}");

// ✅ Correct: conversion failure is expected, not exceptional — no logging
```

### Previous Story Intelligence (Story 1.1)

**Files created in Story 1.1 that this story builds on:**
- `app/Enums/UnitSystem.php` — enum used for `preferredUnit()` and `applyCeilingRounding()`
- `app/Services/UnitConversionService.php` — stub with `UNIT_SYSTEM_SETTING` constant; this story adds methods

**Package already installed (Story 1.1):**
- `mrdth/laravel-model-settings` — not used in this story, but available
- `users` table has `settings` JSON column

**Test patterns established:**
- Unit tests use `uses(TestCase::class)` at file level
- `beforeEach()` for service instantiation
- `expect($result)->toBe()` for exact assertions
- `expect($result)->toBeFloat()` for float type checks
- Run with `php artisan test --compact --filter=TestName`

**Completion notes from Story 1.1:**
- The artisan command is `make:msm User` (single colon), not `make::msm User` (double colon)
- Package uses `addSetting()` for new settings (not `setSetting()` as initially documented)

### Git Intelligence

Recent commits show Story 1.1 was just completed:
- `404c2c0 feat: add user unit preference storage for shopping list conversion`

This story continues the same feature branch pattern. The service stub from Story 1.1 is ready for implementation.

### References

- Architecture: Service Contract — `convert()`, `applyCeilingRounding()`, `preferredUnit()` signatures
  [Source: `_bmad-output/planning-artifacts/architecture.md#Service Contract: UnitConversionService`]
- Architecture: Conversion Table — base-unit approach with bridge constants
  [Source: `_bmad-output/planning-artifacts/architecture.md#Service Contract: UnitConversionService`]
- Architecture: Rounding Pattern — ceiling applied once at end of accumulation
  [Source: `_bmad-output/planning-artifacts/architecture.md#Rounding Pattern`]
- Architecture: Pass-Through Pattern — return null, never throw, never return 0.0
  [Source: `_bmad-output/planning-artifacts/architecture.md#Pass-Through & Error Isolation Pattern`]
- Architecture: MeasurementUnit Extension — add cases to existing enum
  [Source: `_bmad-output/planning-artifacts/architecture.md#MeasurementUnit Extension Pattern`]
- Epics: Story 1.2 acceptance criteria
  [Source: `_bmad-output/planning-artifacts/epics.md#Story 1.2`]
- Existing enum: `app/Enums/MeasurementUnit.php`
- Existing enum: `app/Enums/UnitSystem.php`
- Existing service stub: `app/Services/UnitConversionService.php`
- Project context: `_bmad-output/project-context.md`

## Dev Agent Record

### Agent Model Used

GLM-4 (Claude Code)

### Debug Log References

None.

### Completion Notes List

- ✅ Extended `MeasurementUnit` enum with OUNCE, POUND, FLUID_OUNCE cases
- ✅ Updated `isWeight()` to include OUNCE and POUND
- ✅ Updated `isVolume()` to include FLUID_OUNCE
- ✅ Updated `label()` method with new unit labels
- ✅ Implemented `UnitConversionService::convert()` with base-unit conversion approach
- ✅ Implemented `UnitConversionService::applyCeilingRounding()` for metric/imperial rounding
- ✅ Implemented `UnitConversionService::preferredUnit()` for unit system preferences
- ✅ Added private helper methods: `sameSystem()`, `convertVolumeCrossSystem()`, `convertWeightCrossSystem()`, `roundMetric()`, `roundImperial()`
- ✅ All 59 unit tests pass (79 assertions)
- ✅ Pint formatting passed
- ✅ Installed missing `mrdth/laravel-model-settings` package dependency
- ✅ Code review fixes applied: added isImperial() to MeasurementUnit; refactored sameSystem() to use isImperial() instead of hardcoded arrays; deduplicated roundMetric(); added PHPDoc base-unit precondition to applyCeilingRounding(); added cross-system volume conversion tests (tbsp/cup↔fl oz)

### File List

**Created:**
- `tests/Unit/Services/UnitConversionServiceTest.php` — 50 unit tests for conversion service

**Modified:**
- `app/Enums/MeasurementUnit.php` — added OUNCE, POUND, FLUID_OUNCE cases; updated isVolume()/isWeight()/label(); added isImperial()
- `app/Services/UnitConversionService.php` — added conversion table + 3 public methods + 5 private helpers; refactored sameSystem() to use isImperial()
- `_bmad-output/implementation-artifacts/sprint-status.yaml` — updated story status
