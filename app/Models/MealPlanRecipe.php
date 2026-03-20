<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $meal_plan_id
 * @property int $recipe_id
 * @property float $scale_factor
 * @property float $servings_available
 * @property Recipe|null $recipe
 * @property MealPlan|null $mealPlan
 */
class MealPlanRecipe extends Pivot
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meal_plan_recipe';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scale_factor' => 'decimal:2',
        'servings_available' => 'decimal:2',
    ];

    /**
     * Calculate available servings for this recipe in the meal plan.
     */
    public function calculateAvailableServings(): void
    {
        if (!$this->recipe || !$this->mealPlan) {
            return;
        }

        $recipeServings = $this->recipe->servings;
        $planServings = $recipeServings * $this->scale_factor;
        $numberOfPeople = $this->mealPlan->people_count;

        if ($numberOfPeople <= 0) {
            $this->servings_available = 0;
            return;
        }

        $this->servings_available = $planServings / $numberOfPeople;
    }

    /**
     * Get the meal plan that owns the pivot.
     *
     * @return BelongsTo<MealPlan, $this>
     */
    public function mealPlan(): BelongsTo
    {
        return $this->belongsTo(MealPlan::class);
    }

    /**
     * Get the recipe that owns the pivot.
     *
     * @return BelongsTo<Recipe, $this>
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
