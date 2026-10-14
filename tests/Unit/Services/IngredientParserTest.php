<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;
use App\Services\IngredientParser;

test('parses plural units without capturing the suffix in the name', function () {
    $parsed = (new IngredientParser())->parse('2 cups flour');

    expect($parsed['ingredient']->name)->toBe('flour')
        ->and($parsed['amount'])->toBe(2.0)
        ->and($parsed['unit'])->toBe(MeasurementUnit::CUP);
});

test('parses abbreviated units that have no plural suffix', function () {
    $parsed = (new IngredientParser())->parse('1 tbsp olive oil');

    expect($parsed['ingredient']->name)->toBe('olive oil')
        ->and($parsed['amount'])->toBe(1.0)
        ->and($parsed['unit'])->toBe(MeasurementUnit::TABLESPOON);
});

test('parses cans as a unit', function () {
    $parsed = (new IngredientParser())->parse('1 can chickpeas');

    expect($parsed['ingredient']->name)->toBe('chickpeas')
        ->and($parsed['amount'])->toBe(1.0)
        ->and($parsed['unit'])->toBe(MeasurementUnit::CAN);
});

test('parses tins as a synonym for cans', function () {
    $parsed = (new IngredientParser())->parse('1 tin of chopped tomatoes');

    expect($parsed['ingredient']->name)->toBe('chopped tomatoes')
        ->and($parsed['amount'])->toBe(1.0)
        ->and($parsed['unit'])->toBe(MeasurementUnit::CAN);
});
