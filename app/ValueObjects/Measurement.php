<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\MeasurementUnit;
use JsonSerializable;

class Measurement implements JsonSerializable, \Stringable
{
    public function __construct(
        public readonly ?float $amount = null,
        public readonly ?MeasurementUnit $unit = null,
    ) {
    }

    public static function from(?float $amount, ?string $unit = null): self
    {
        if ($unit !== null && $unit !== '' && $unit !== '0') {
            $unitEnum = MeasurementUnit::tryFrom($unit);
            return new self($amount, $unitEnum);
        }

        return new self($amount, null);
    }

    /**
     * Scale the measurement by a factor.
     *
     * @param float $factor The scaling factor to apply
     * @return self A new Measurement instance with the scaled amount
     */
    public function scale(float $factor): self
    {
        if ($this->amount === null) {
            return new self(null, $this->unit);
        }

        $scaledAmount = $this->amount * $factor;

        // Apply smart rounding based on the unit type and quantity
        // For very small amounts (< 0.1), we want to preserve the exact calculation
        // without rounding to maintain precision
        if ($this->unit && $scaledAmount >= 0.1) {
            $scaledAmount = $this->applySmartRounding($scaledAmount);
        }

        return new self($scaledAmount, $this->unit);
    }

    /**
     * Apply smart rounding to avoid awkward measurements.
     *
     * @param float $amount The amount to round
     * @return float The rounded amount
     */
    private function applySmartRounding(float $amount): float
    {
        // For very small amounts, preserve exact values
        if ($amount < 0.1) {
            return $amount;
        }

        // For small amounts, round to 1 decimal place
        if ($amount < 1) {
            return round($amount, 1);
        }

        // For teaspoons and tablespoons, use specific fractions
        if ($this->unit === MeasurementUnit::TEASPOON || $this->unit === MeasurementUnit::TABLESPOON) {
            // Round to the nearest 1/4 for teaspoons and tablespoons
            return round($amount * 4) / 4;
        }

        // For cups, use specific fractions
        if ($this->unit === MeasurementUnit::CUP) {
            // Round to the nearest 1/8 for cups
            return round($amount * 8) / 8;
        }

        // For weight units, round to nearest 0.5 if less than 10, otherwise to nearest integer
        if ($this->unit?->isWeight() === true) {
            if ($amount < 10) {
                return round($amount * 2) / 2;
            }
            return round($amount);
        }

        // For volume units, round to nearest 0.5 if less than 10, otherwise to nearest integer
        if ($this->unit?->isVolume() === true) {
            if ($amount < 10) {
                return round($amount * 2) / 2;
            }
            return round($amount);
        }

        // For count units, always round to nearest integer
        if ($this->unit?->isUnit() === true) {
            return round($amount);
        }

        // Default rounding to 1 decimal place
        return round($amount, 1);
    }

    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->amount,
            'unit' => $this->unit?->value,
        ];
    }

    public function __toString(): string
    {
        if ($this->amount === null) {
            return $this->unit?->value ?? '';
        }

        if (!$this->unit instanceof \App\Enums\MeasurementUnit) {
            return (string) $this->amount;
        }

        return "{$this->amount} {$this->unit->value}";
    }

    public function format(): string
    {
        if ($this->amount === null) {
            return $this->unit?->value ?? '';
        }

        $amount = number_format($this->amount, 2);
        // Remove trailing zeros after decimal point
        $amount = rtrim(rtrim($amount, '0'), '.');

        if (!$this->unit instanceof \App\Enums\MeasurementUnit) {
            return $amount;
        }

        return "{$amount} {$this->unit->value}";
    }
}
