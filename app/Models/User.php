<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Enums\UnitSystem;
use App\Services\UnitConversionService;
use Laravel\Sanctum\HasApiTokens;
use Mrdth\LaravelModelSettings\Concerns\HasSettings;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasSettings;
    use Notifiable;
    use HasSlug;
    use HasApiTokens;

    /**
     * The attributes that should be visible in arrays.
     *
     * @var list<string>
     */
    protected $visible = [
        'id',
        'name',
        'slug',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_user_favorites')
            ->withTimestamps();
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }

    public function shoppingLists(): HasMany
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->email, config('auth.admin_emails'));
    }

    public function unitSystem(): UnitSystem
    {
        return UnitSystem::from(
            $this->getSetting(UnitConversionService::UNIT_SYSTEM_SETTING, UnitSystem::Metric->value)
        );
    }
}
