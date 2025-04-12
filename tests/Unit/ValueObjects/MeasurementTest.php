<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;
use App\ValueObjects\Measurement;

test('it can be created from amount and unit string', function () {
    $measurement = Measurement::from(2.5, 'cup');

    expect($measurement->amount)->toBe(2.5);
    expect($measurement->unit)->toBe(MeasurementUnit::CUP);
});

test('it can be converted to json', function () {
    $measurement = new Measurement(2.5, MeasurementUnit::CUP);

    expect($measurement->jsonSerialize())->toBe([
        'amount' => 2.5,
        'unit' => 'cup',
    ]);
});

test('it can be converted to string', function () {
    $measurement = new Measurement(2.5, MeasurementUnit::CUP);

    expect((string) $measurement)->toBe('2.5 cup');
});

test('it formats measurements nicely', function () {
    $measurements = [
        new Measurement(2.5, MeasurementUnit::CUP),
        new Measurement(2.0, MeasurementUnit::GRAM),
        new Measurement(2.50, MeasurementUnit::MILLILITER),
    ];

    expect($measurements[0]->format())->toBe('2.5 cup');
    expect($measurements[1]->format())->toBe('2 g');
    expect($measurements[2]->format())->toBe('2.5 ml');
});

test('it can scale measurements by a factor', function () {
    $measurement = new Measurement(2.0, MeasurementUnit::CUP);
    $scaled = $measurement->scale(2.0);

    expect($scaled->amount)->toBe(4.0);
    expect($scaled->unit)->toBe(MeasurementUnit::CUP);

    // Original measurement should remain unchanged
    expect($measurement->amount)->toBe(2.0);
});

test('it applies smart rounding for teaspoons when scaling', function () {
    $measurement = new Measurement(1.0, MeasurementUnit::TEASPOON);

    // Should round to nearest 1/4 teaspoon
    expect($measurement->scale(1.1)->amount)->toBe(1.0);
    expect($measurement->scale(1.2)->amount)->toBe(1.25);
    expect($measurement->scale(1.3)->amount)->toBe(1.25);
    expect($measurement->scale(1.4)->amount)->toBe(1.5);
});

test('it applies smart rounding for tablespoons when scaling', function () {
    $measurement = new Measurement(1.0, MeasurementUnit::TABLESPOON);

    // Should round to nearest 1/4 tablespoon
    expect($measurement->scale(1.1)->amount)->toBe(1.0);
    expect($measurement->scale(1.2)->amount)->toBe(1.25);
    expect($measurement->scale(1.3)->amount)->toBe(1.25);
    expect($measurement->scale(1.4)->amount)->toBe(1.5);
});

test('it applies smart rounding for cups when scaling', function () {
    $measurement = new Measurement(1.0, MeasurementUnit::CUP);

    // Should round to nearest 1/8 cup
    expect($measurement->scale(1.05)->amount)->toBe(1.0);
    expect($measurement->scale(1.1)->amount)->toBe(1.125);
    expect($measurement->scale(1.2)->amount)->toBe(1.25);
    expect($measurement->scale(1.3)->amount)->toBe(1.25);
});

test('it applies smart rounding for weight units when scaling', function () {
    $measurement = new Measurement(5.0, MeasurementUnit::GRAM);

    // Should round to nearest 0.5 for small weights
    expect($measurement->scale(1.1)->amount)->toBe(5.5);
    expect($measurement->scale(1.2)->amount)->toBe(6.0);

    // Should round to nearest integer for larger weights
    $largeMeasurement = new Measurement(20.0, MeasurementUnit::GRAM);
    expect($largeMeasurement->scale(1.1)->amount)->toBe(22.0);
    expect($largeMeasurement->scale(1.2)->amount)->toBe(24.0);
});

test('it applies smart rounding for volume units when scaling', function () {
    $measurement = new Measurement(5.0, MeasurementUnit::MILLILITER);

    // Should round to nearest 0.5 for small volumes
    expect($measurement->scale(1.1)->amount)->toBe(5.5);
    expect($measurement->scale(1.2)->amount)->toBe(6.0);

    // Should round to nearest integer for larger volumes
    $largeMeasurement = new Measurement(20.0, MeasurementUnit::MILLILITER);
    expect($largeMeasurement->scale(1.1)->amount)->toBe(22.0);
    expect($largeMeasurement->scale(1.2)->amount)->toBe(24.0);
});

test('it rounds count units to integers when scaling', function () {
    $measurement = new Measurement(1.0, MeasurementUnit::PIECE);

    expect($measurement->scale(1.2)->amount)->toBe(1.0);
    expect($measurement->scale(1.5)->amount)->toBe(2.0);
    expect($measurement->scale(2.2)->amount)->toBe(2.0);
    expect($measurement->scale(2.6)->amount)->toBe(3.0);
});

test('it handles very small amounts when scaling', function () {
    $measurement = new Measurement(0.05, MeasurementUnit::TEASPOON);

    // Test with factor of 2.0
    $scaled1 = $measurement->scale(2.0);
    expect($scaled1->amount)->toBe(0.1);

    // Test with factor of 3.0 - using toEqual instead of toBe to handle floating point precision
    $scaled2 = $measurement->scale(3.0);
    expect($scaled2->amount)->toEqual(0.2);

    // Test with factor of 4.0
    $scaled3 = $measurement->scale(4.0);
    expect($scaled3->amount)->toEqual(0.2);
});

test('it handles scaling with null unit', function () {
    $measurement = new Measurement(2.0, null);
    $scaled = $measurement->scale(1.5);

    expect($scaled->amount)->toBe(3.0);
    expect($scaled->unit)->toBeNull();
});

test('it handles null amounts', function () {
    $measurement = new Measurement(null, MeasurementUnit::CUP);
    
    expect($measurement->amount)->toBeNull();
    expect($measurement->unit)->toBe(MeasurementUnit::CUP);
});

test('it can be created with null amount from static factory', function () {
    $measurement = Measurement::from(null, 'cup');

    expect($measurement->amount)->toBeNull();
    expect($measurement->unit)->toBe(MeasurementUnit::CUP);
});

test('it converts null amounts to empty strings with unit', function () {
    $measurement = new Measurement(null, MeasurementUnit::CUP);
    
    expect((string) $measurement)->toBe('cup');
    expect($measurement->format())->toBe('cup');
});

test('it formats null amounts with null unit as empty string', function () {
    $measurement = new Measurement(null, null);
    
    expect((string) $measurement)->toBe('');
    expect($measurement->format())->toBe('');
});

test('it returns null amount when scaling null amount', function () {
    $measurement = new Measurement(null, MeasurementUnit::CUP);
    $scaled = $measurement->scale(2.0);
    
    expect($scaled->amount)->toBeNull();
    expect($scaled->unit)->toBe(MeasurementUnit::CUP);
});

test('it serializes null amounts to JSON correctly', function () {
    $measurement = new Measurement(null, MeasurementUnit::CUP);
    
    expect($measurement->jsonSerialize())->toBe([
        'amount' => null,
        'unit' => 'cup',
    ]);
});
