<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MeasurementUnit;
use App\Models\Ingredient;

class IngredientParser
{
    public function parse(string $ingredient_string): array
    {
        // Common unit patterns
        $unit_patterns = [
            'g' => MeasurementUnit::GRAM,
            'gram(s)?' => MeasurementUnit::GRAM,
            'kg' => MeasurementUnit::KILOGRAM,
            'kilo(s)?' => MeasurementUnit::KILOGRAM,
            'kilogram(s)?' => MeasurementUnit::KILOGRAM,
            'ml' => MeasurementUnit::MILLILITER,
            'milliliter(s)?' => MeasurementUnit::MILLILITER,
            'l' => MeasurementUnit::LITER,
            'liter(s)?' => MeasurementUnit::LITER,
            'tsp' => MeasurementUnit::TEASPOON,
            'teaspoon(s)?' => MeasurementUnit::TEASPOON,
            'tbsp' => MeasurementUnit::TABLESPOON,
            'tablespoon(s)?' => MeasurementUnit::TABLESPOON,
            'cup(s)?' => MeasurementUnit::CUP,
            'piece(s)?' => MeasurementUnit::PIECE,
            'pc(s)?' => MeasurementUnit::PIECE,
            'pinch(es)?' => MeasurementUnit::PINCH,
        ];

        // Common fraction patterns
        $fraction_patterns = [
            '½' => '0.5',
            '⅓' => '0.333',
            '⅔' => '0.667',
            '¼' => '0.25',
            '¾' => '0.75',
            '⅕' => '0.2',
            '⅖' => '0.4',
            '⅗' => '0.6',
            '⅘' => '0.8',
            '⅙' => '0.167',
            '⅚' => '0.833',
            '⅐' => '0.143',
            '⅛' => '0.125',
            '⅜' => '0.375',
            '⅝' => '0.625',
            '⅞' => '0.875',
        ];

        // Replace unicode fractions with decimal equivalents
        foreach ($fraction_patterns as $fraction => $decimal) {
            $ingredient_string = str_replace($fraction, $decimal, $ingredient_string);
        }

        // Extract amount and unit
        $amount = 0;
        $unit = MeasurementUnit::PIECE;
        $name = $ingredient_string;

        // Look for number at start of string (including decimals and fractions)
        if (preg_match('/^([\d.\/]+)\s*(.*)$/', $ingredient_string, $matches)) {
            // Convert fraction to decimal if necessary
            if (str_contains($matches[1], '/')) {
                [$numerator, $denominator] = explode('/', $matches[1]);
                $amount = (float) $numerator / (float) $denominator;
            } else {
                $amount = (float) $matches[1];
            }
            $name = $matches[2];

            // Look for unit after the number
            foreach ($unit_patterns as $pattern => $unit_enum) {
                if (preg_match("/^($pattern)\s+(.+)$/i", $name, $unit_matches)) {
                    $unit = $unit_enum;
                    $name = $unit_matches[2];
                    break;
                }
            }
        }

        // Clean up the ingredient name
        $name = trim($name);
        $name = preg_replace('/^of\s+/', '', $name); // Remove leading "of"
        $name = preg_replace('/,.*$/', '', (string) $name); // Remove anything after a comma
        $name = trim((string) $name);

        // Find or create the ingredient
        $ingredient = Ingredient::firstOrCreate(
            ['name' => $name]
        );

        return [
            'ingredient' => $ingredient,
            'amount' => $amount,
            'unit' => $unit,
        ];
    }
}
