<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MeasurementUnit;
use App\ValueObjects\Measurement;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RecipeIngredient extends Pivot
{
    protected $casts = [
        'amount' => 'float',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function measurement(): Measurement
    {
        $unit = $this->unit;

        // Convert string unit to enum for ValueObject if possible
        if (is_string($unit)) {
            $unit = MeasurementUnit::tryFrom($unit) ?? MeasurementUnit::PIECE;
        }

        return new Measurement(
            amount: $this->amount,
            unit: $unit,
        );
    }
}
