<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MeasurementUnit;
use App\ValueObjects\Measurement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property bool $is_favorited
 * @property RecipeIngredient $pivot
 */
class Recipe extends Model
{
    use HasFactory;
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $casts = [
        'cooking_time' => 'integer',
        'prep_time' => 'integer',
        'servings' => 'integer',
        'images' => 'array',
        'is_public' => 'boolean',
    ];

    protected $hidden = [
        'user_id',
        'updated_at',
    ];

    public function isImported(): bool
    {
        return $this->url !== null && $this->url !== '';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<Ingredient, $this, RecipeIngredient>
     */
    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->withPivot(['amount', 'unit', 'description'])
            ->using(RecipeIngredient::class);
    }

    /**
     * @return BelongsToMany<Collection, $this>
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    /**
     * @return HasOne<NutritionInformation, $this>
     */
    public function nutritionInformation(): HasOne
    {
        return $this->hasOne(NutritionInformation::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recipe_user_favorites')
            ->withTimestamps();
    }

    public function getMeasurementForIngredient(Ingredient $ingredient): ?Measurement
    {
        $pivot = $this->ingredients->find($ingredient)?->pivot;

        if (! $pivot) {
            return null;
        }

        $unit = $pivot->unit;
        if ($unit instanceof MeasurementUnit) {
            $unitValue = $unit->value;
        } elseif (is_string($unit)) {
            $unitValue = $unit;
        } else {
            $unitValue = null;
        }

        return Measurement::from(
            amount: $pivot->amount,
            unit: $unitValue,
        );
    }
}
