<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MeasurementUnit;
use App\Enums\UnitSystem;

class UnitConversionService
{
    public const string UNIT_SYSTEM_SETTING = 'unit_system';

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

    /**
     * Apply ceiling rounding per PRD spec.
     * Metric: nearest 5ml (volume) / nearest 5g (weight)
     * Imperial: nearest 5 fl oz (volume) / nearest 0.1 oz (weight)
     *
     * IMPORTANT: $amount must already be expressed in the dimension's base unit:
     * - Metric volume → ml, Metric weight → g
     * - Imperial volume → fl oz, Imperial weight → oz
     */
    public function applyCeilingRounding(float $amount, MeasurementUnit $unit, UnitSystem $system): float
    {
        return match ($system) {
            UnitSystem::Metric => $this->roundMetric($amount, $unit),
            UnitSystem::Imperial => $this->roundImperial($amount, $unit),
        };
    }

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

    private function sameSystem(MeasurementUnit $from, MeasurementUnit $to): bool
    {
        return $from->isImperial() === $to->isImperial();
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

    private function roundMetric(float $amount, MeasurementUnit $unit): float
    {
        if ($unit->isVolume() || $unit->isWeight()) {
            // Ceiling to nearest 5ml (volume) or 5g (weight)
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
}
