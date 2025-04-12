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
        ]);
    }

    public function isWeight(): bool
    {
        return in_array($this, [
            self::GRAM,
            self::KILOGRAM,
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
}
