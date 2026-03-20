<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $meal_plan_day_id
 * @property int $meal_plan_recipe_id
 * @property float $servings
 * @property bool $to_cook
 * @property MealPlanDay|null $mealPlanDay
 * @property MealPlanRecipe|null $mealPlanRecipe
 */
class MealAssignment extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'servings' => 'decimal:2',
        'to_cook' => 'boolean',
    ];

    /**
     * Get the meal plan day that owns the assignment.
     *
     * @return BelongsTo<MealPlanDay, $this>
     */
    public function mealPlanDay(): BelongsTo
    {
        return $this->belongsTo(MealPlanDay::class);
    }

    /**
     * Get the meal plan recipe that owns the assignment.
     *
     * @return BelongsTo<MealPlanRecipe, $this>
     */
    public function mealPlanRecipe(): BelongsTo
    {
        return $this->belongsTo(MealPlanRecipe::class);
    }
}
