<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;

test('it provides human readable labels', function () {
    expect(MeasurementUnit::GRAM->label())->toBe('Gram');
    expect(MeasurementUnit::MILLILITER->label())->toBe('Milliliter');
    expect(MeasurementUnit::CUP->label())->toBe('Cup');
    expect(MeasurementUnit::CAN->label())->toBe('Can');
});

test('it identifies volume units', function () {
    expect(MeasurementUnit::MILLILITER->isVolume())->toBeTrue();
    expect(MeasurementUnit::LITER->isVolume())->toBeTrue();
    expect(MeasurementUnit::TEASPOON->isVolume())->toBeTrue();
    expect(MeasurementUnit::TABLESPOON->isVolume())->toBeTrue();
    expect(MeasurementUnit::CUP->isVolume())->toBeTrue();

    expect(MeasurementUnit::GRAM->isVolume())->toBeFalse();
    expect(MeasurementUnit::PIECE->isVolume())->toBeFalse();
});

test('it identifies weight units', function () {
    expect(MeasurementUnit::GRAM->isWeight())->toBeTrue();
    expect(MeasurementUnit::KILOGRAM->isWeight())->toBeTrue();

    expect(MeasurementUnit::MILLILITER->isWeight())->toBeFalse();
    expect(MeasurementUnit::PIECE->isWeight())->toBeFalse();
});

test('it identifies unit measurements', function () {
    expect(MeasurementUnit::PIECE->isUnit())->toBeTrue();
    expect(MeasurementUnit::PINCH->isUnit())->toBeTrue();
    expect(MeasurementUnit::CAN->isUnit())->toBeTrue();

    expect(MeasurementUnit::GRAM->isUnit())->toBeFalse();
    expect(MeasurementUnit::MILLILITER->isUnit())->toBeFalse();
});
