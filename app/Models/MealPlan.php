<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $people_count
 * @property int $duration
 * @property Carbon $start_date
 * @property Carbon $end_date
 */
class MealPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'duration' => 'integer',
        'people_count' => 'integer',
    ];

    /**
     * Get the user that owns the meal plan.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipes for the meal plan.
     *
     * @return BelongsToMany<Recipe, $this, MealPlanRecipe>
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)
            ->using(MealPlanRecipe::class)
            ->withPivot(['id', 'scale_factor', 'servings_available'])
            ->withTimestamps();
    }

    /**
     * Get the days for the meal plan.
     *
     * @return HasMany<MealPlanDay, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(MealPlanDay::class)->orderBy('day_number');
    }

    /**
     * Get the end date of the meal plan.
     */
    public function getEndDateAttribute(): Carbon
    {
        return $this->start_date->copy()->addDays($this->duration - 1);
    }
}
