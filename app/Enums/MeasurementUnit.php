<?php

declare(strict_types=1);

namespace App\Enums;

enum MeasurementUnit: string
{
    case GRAM = 'g';
    case KILOGRAM = 'kg';
    case MILLILITER = 'ml';
    case LITER = 'l';
    case TEASPOON = 'tsp';
    case TABLESPOON = 'tbsp';
    case CUP = 'cup';
    case PIECE = 'pc';
    case PINCH = 'pinch';
    case CLOVE = 'clove';
    case OUNCE = 'oz';
    case POUND = 'lb';
    case FLUID_OUNCE = 'fl oz';

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

    public function isWeight(): bool
    {
        return in_array($this, [
            self::GRAM,
            self::KILOGRAM,
            self::OUNCE,
            self::POUND,
        ]);
    }

    public function isUnit(): bool
    {
        return in_array($this, [
            self::PIECE,
            self::PINCH,
            self::CLOVE,
        ]);
    }

    public function isImperial(): bool
    {
        return in_array($this, [
            self::OUNCE,
            self::POUND,
            self::FLUID_OUNCE,
        ]);
    }
}
