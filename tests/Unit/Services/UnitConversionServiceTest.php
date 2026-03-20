<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;
use App\Services\UnitConversionService;

beforeEach(function () {
    $this->service = new UnitConversionService();
});

// ============================================
// MeasurementUnit Enum Tests (AC: 16)
// ============================================

test('MeasurementUnit has OUNCE case', function () {
    expect(MeasurementUnit::OUNCE->value)->toBe('oz');
});

test('MeasurementUnit has POUND case', function () {
    expect(MeasurementUnit::POUND->value)->toBe('lb');
});

test('MeasurementUnit has FLUID_OUNCE case', function () {
    expect(MeasurementUnit::FLUID_OUNCE->value)->toBe('fl oz');
});

test('OUNCE is a weight unit', function () {
    expect(MeasurementUnit::OUNCE->isWeight())->toBeTrue();
});

test('POUND is a weight unit', function () {
    expect(MeasurementUnit::POUND->isWeight())->toBeTrue();
});

test('FLUID_OUNCE is a volume unit', function () {
    expect(MeasurementUnit::FLUID_OUNCE->isVolume())->toBeTrue();
});

test('OUNCE is an imperial unit', function () {
    expect(MeasurementUnit::OUNCE->isImperial())->toBeTrue();
});

test('POUND is an imperial unit', function () {
    expect(MeasurementUnit::POUND->isImperial())->toBeTrue();
});

test('FLUID_OUNCE is an imperial unit', function () {
    expect(MeasurementUnit::FLUID_OUNCE->isImperial())->toBeTrue();
});

test('GRAM is not an imperial unit', function () {
    expect(MeasurementUnit::GRAM->isImperial())->toBeFalse();
});

test('MILLILITER is not an imperial unit', function () {
    expect(MeasurementUnit::MILLILITER->isImperial())->toBeFalse();
});

test('OUNCE has correct label', function () {
    expect(MeasurementUnit::OUNCE->label())->toBe('Ounce');
});

test('POUND has correct label', function () {
    expect(MeasurementUnit::POUND->label())->toBe('Pound');
});

test('FLUID_OUNCE has correct label', function () {
    expect(MeasurementUnit::FLUID_OUNCE->label())->toBe('Fluid Ounce');
});

// ============================================
// convert() method tests (AC: 1, 2, 3, 4, 5, 6)
// ============================================

// Volume-to-volume conversions (AC: 1)
test('convert converts tablespoons to milliliters', function () {
    $result = $this->service->convert(2.0, MeasurementUnit::TABLESPOON, MeasurementUnit::MILLILITER);
    expect($result)->toBe(30.0);
});

test('convert converts teaspoons to milliliters', function () {
    $result = $this->service->convert(3.0, MeasurementUnit::TEASPOON, MeasurementUnit::MILLILITER);
    expect($result)->toBe(15.0);
});

test('convert converts cups to milliliters', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::CUP, MeasurementUnit::MILLILITER);
    expect($result)->toBe(240.0);
});

test('convert converts milliliters to liters', function () {
    $result = $this->service->convert(500.0, MeasurementUnit::MILLILITER, MeasurementUnit::LITER);
    expect($result)->toBe(0.5);
});

test('convert converts liters to milliliters', function () {
    $result = $this->service->convert(1.5, MeasurementUnit::LITER, MeasurementUnit::MILLILITER);
    expect($result)->toBe(1500.0);
});

// Metric-to-imperial volume conversion (AC: 2)
test('convert converts milliliters to fluid ounces', function () {
    $result = $this->service->convert(240.0, MeasurementUnit::MILLILITER, MeasurementUnit::FLUID_OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(8.0)->toBeLessThan(8.2);
});

// Imperial-to-metric weight conversion (AC: 3)
test('convert converts ounces to grams', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::OUNCE, MeasurementUnit::GRAM);
    expect($result)->toBeFloat()->toBeGreaterThan(28.0)->toBeLessThan(29.0);
});

test('convert converts pounds to kilograms', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::POUND, MeasurementUnit::KILOGRAM);
    expect($result)->toBeFloat()->toBeGreaterThan(0.45)->toBeLessThan(0.46);
});

// Metric-to-imperial weight conversion (AC: 4)
test('convert converts grams to ounces', function () {
    $result = $this->service->convert(100.0, MeasurementUnit::GRAM, MeasurementUnit::OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(3.5)->toBeLessThan(3.6);
});

test('convert converts kilograms to pounds', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::KILOGRAM, MeasurementUnit::POUND);
    expect($result)->toBeFloat()->toBeGreaterThan(2.2)->toBeLessThan(2.3);
});

// Weight-to-weight same system
test('convert converts grams to kilograms', function () {
    $result = $this->service->convert(500.0, MeasurementUnit::GRAM, MeasurementUnit::KILOGRAM);
    expect($result)->toBe(0.5);
});

test('convert converts kilograms to grams', function () {
    $result = $this->service->convert(2.0, MeasurementUnit::KILOGRAM, MeasurementUnit::GRAM);
    expect($result)->toBe(2000.0);
});

// Cross-dimension returns null (AC: 5)
test('convert returns null for cross-dimension conversion (volume to weight)', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::CUP, MeasurementUnit::GRAM);
    expect($result)->toBeNull();
});

test('convert returns null for cross-dimension conversion (weight to volume)', function () {
    $result = $this->service->convert(100.0, MeasurementUnit::GRAM, MeasurementUnit::MILLILITER);
    expect($result)->toBeNull();
});

// Dimensionless returns null (AC: 6)
test('convert returns null for dimensionless source unit', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::PIECE, MeasurementUnit::GRAM);
    expect($result)->toBeNull();
});

test('convert returns null for dimensionless target unit', function () {
    $result = $this->service->convert(100.0, MeasurementUnit::GRAM, MeasurementUnit::PIECE);
    expect($result)->toBeNull();
});

test('convert returns null for pinch unit', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::PINCH, MeasurementUnit::GRAM);
    expect($result)->toBeNull();
});

test('convert returns null for clove unit', function () {
    $result = $this->service->convert(1.0, MeasurementUnit::CLOVE, MeasurementUnit::MILLILITER);
    expect($result)->toBeNull();
});

// Same unit returns same amount
test('convert returns same amount when from and to are same unit', function () {
    $result = $this->service->convert(50.0, MeasurementUnit::GRAM, MeasurementUnit::GRAM);
    expect($result)->toBe(50.0);
});

// Imperial volume to metric volume
test('convert converts fluid ounces to milliliters', function () {
    $result = $this->service->convert(8.0, MeasurementUnit::FLUID_OUNCE, MeasurementUnit::MILLILITER);
    expect($result)->toBeFloat()->toBeGreaterThan(236.0)->toBeLessThan(237.0);
});

// Cross-system volume: non-ml metric → fl oz (covers convertVolumeCrossSystem non-FLUID_OUNCE from path)
test('convert converts tablespoons to fluid ounces', function () {
    // 1 tbsp = 15ml / 29.5735 ≈ 0.507 fl oz
    $result = $this->service->convert(1.0, MeasurementUnit::TABLESPOON, MeasurementUnit::FLUID_OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(0.50)->toBeLessThan(0.52);
});

test('convert converts cups to fluid ounces', function () {
    // 1 cup = 240ml / 29.5735 ≈ 8.115 fl oz
    $result = $this->service->convert(1.0, MeasurementUnit::CUP, MeasurementUnit::FLUID_OUNCE);
    expect($result)->toBeFloat()->toBeGreaterThan(8.0)->toBeLessThan(8.2);
});

test('convert converts fluid ounces to tablespoons', function () {
    // 1 fl oz = 29.5735ml / 15 ≈ 1.972 tbsp
    $result = $this->service->convert(1.0, MeasurementUnit::FLUID_OUNCE, MeasurementUnit::TABLESPOON);
    expect($result)->toBeFloat()->toBeGreaterThan(1.9)->toBeLessThan(2.0);
});

test('convert converts fluid ounces to cups', function () {
    // 8 fl oz = 8 * 29.5735ml / 240 ≈ 0.986 cups
    $result = $this->service->convert(8.0, MeasurementUnit::FLUID_OUNCE, MeasurementUnit::CUP);
    expect($result)->toBeFloat()->toBeGreaterThan(0.98)->toBeLessThan(1.0);
});

// ============================================
// applyCeilingRounding() method tests (AC: 7, 8, 9, 10)
// ============================================

// Metric volume ceiling rounding (AC: 7)
test('applyCeilingRounding rounds metric volume to nearest 5ml', function () {
    $result = $this->service->applyCeilingRounding(112.0, MeasurementUnit::MILLILITER, UnitSystem::Metric);
    expect($result)->toBe(115.0);
});

test('applyCeilingRounding rounds exact multiple metric volume', function () {
    $result = $this->service->applyCeilingRounding(110.0, MeasurementUnit::MILLILITER, UnitSystem::Metric);
    expect($result)->toBe(110.0);
});

test('applyCeilingRounding rounds small metric volume', function () {
    $result = $this->service->applyCeilingRounding(3.0, MeasurementUnit::MILLILITER, UnitSystem::Metric);
    expect($result)->toBe(5.0);
});

// Metric weight ceiling rounding (AC: 8)
test('applyCeilingRounding rounds metric weight to nearest 5g', function () {
    $result = $this->service->applyCeilingRounding(103.0, MeasurementUnit::GRAM, UnitSystem::Metric);
    expect($result)->toBe(105.0);
});

test('applyCeilingRounding rounds exact multiple metric weight', function () {
    $result = $this->service->applyCeilingRounding(100.0, MeasurementUnit::GRAM, UnitSystem::Metric);
    expect($result)->toBe(100.0);
});

// Imperial volume ceiling rounding (AC: 9)
test('applyCeilingRounding rounds imperial volume to nearest 5 fl oz', function () {
    $result = $this->service->applyCeilingRounding(3.2, MeasurementUnit::FLUID_OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(5.0);
});

test('applyCeilingRounding rounds exact multiple imperial volume', function () {
    $result = $this->service->applyCeilingRounding(10.0, MeasurementUnit::FLUID_OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(10.0);
});

// Imperial weight ceiling rounding (AC: 10)
test('applyCeilingRounding rounds imperial weight to nearest 0.1 oz', function () {
    $result = $this->service->applyCeilingRounding(1.15, MeasurementUnit::OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(1.2);
});

test('applyCeilingRounding rounds exact multiple imperial weight', function () {
    $result = $this->service->applyCeilingRounding(1.5, MeasurementUnit::OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(1.5);
});

test('applyCeilingRounding rounds up imperial weight', function () {
    $result = $this->service->applyCeilingRounding(1.11, MeasurementUnit::OUNCE, UnitSystem::Imperial);
    expect($result)->toBe(1.2);
});

// ============================================
// preferredUnit() method tests (AC: 11, 12, 13, 14, 15)
// ============================================

// Preferred unit: volume + metric (AC: 11)
test('preferredUnit returns MILLILITER for volume source with metric system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::TABLESPOON, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::MILLILITER);
});

test('preferredUnit returns MILLILITER for fluid ounce source with metric system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::FLUID_OUNCE, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::MILLILITER);
});

// Preferred unit: volume + imperial (AC: 12)
test('preferredUnit returns FLUID_OUNCE for volume source with imperial system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::MILLILITER, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::FLUID_OUNCE);
});

test('preferredUnit returns FLUID_OUNCE for tablespoon source with imperial system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::TABLESPOON, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::FLUID_OUNCE);
});

// Preferred unit: weight + metric (AC: 13)
test('preferredUnit returns GRAM for weight source with metric system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::OUNCE, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::GRAM);
});

test('preferredUnit returns GRAM for pound source with metric system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::POUND, UnitSystem::Metric);
    expect($result)->toBe(MeasurementUnit::GRAM);
});

// Preferred unit: weight + imperial (AC: 14)
test('preferredUnit returns OUNCE for weight source with imperial system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::GRAM, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::OUNCE);
});

test('preferredUnit returns OUNCE for kilogram source with imperial system', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::KILOGRAM, UnitSystem::Imperial);
    expect($result)->toBe(MeasurementUnit::OUNCE);
});

// Preferred unit: dimensionless returns null (AC: 15)
test('preferredUnit returns null for dimensionless units', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::PIECE, UnitSystem::Metric);
    expect($result)->toBeNull();
});

test('preferredUnit returns null for pinch unit', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::PINCH, UnitSystem::Metric);
    expect($result)->toBeNull();
});

test('preferredUnit returns null for clove unit', function () {
    $result = $this->service->preferredUnit(MeasurementUnit::CLOVE, UnitSystem::Imperial);
    expect($result)->toBeNull();
});
