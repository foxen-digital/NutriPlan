<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $meal_plan_id
 * @property int $day_number
 */
class MealPlanDay extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'day_number' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'date',
    ];

    /**
     * Get the meal plan that owns the day.
     *
     * @return BelongsTo<MealPlan, $this>
     */
    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    /**
     * Get the meal assignments for the day.
     */
    public function mealAssignments(): HasMany
    {
        return $this->hasMany(MealAssignment::class);
    }

    /**
     * Get the date for this day.
     */
    public function getDateAttribute(): string
    {
        if (!$this->relationLoaded('mealPlan')) {
            $this->load('mealPlan');
        }

        return $this->mealPlan->start_date->copy()->addDays($this->day_number - 1)->toDateString();
    }
}
