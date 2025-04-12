<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Recipe;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Ingredient;
use App\Enums\MeasurementUnit;
use App\ValueObjects\Measurement;
use Spatie\Sluggable\SlugOptions;
use App\Models\NutritionInformation;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

test('recipe has correct relationships', function () {
    $recipe = new Recipe();

    expect($recipe->user())->toBeInstanceOf(BelongsTo::class)
        ->and($recipe->categories())->toBeInstanceOf(BelongsToMany::class)
        ->and($recipe->ingredients())->toBeInstanceOf(BelongsToMany::class)
        ->and($recipe->collections())->toBeInstanceOf(BelongsToMany::class)
        ->and($recipe->nutritionInformation())->toBeInstanceOf(HasOne::class)
        ->and($recipe->favoritedBy())->toBeInstanceOf(BelongsToMany::class);
});

test('recipe belongs to a user', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    expect($recipe->user)->toBeInstanceOf(User::class)
        ->and($recipe->user->id)->toBe($user->id);
});

test('recipe can have multiple categories', function () {
    $recipe = Recipe::factory()->create();
    $categories = Category::factory()->count(3)->create();

    $recipe->categories()->attach($categories);

    expect($recipe->categories)->toHaveCount(3)
        ->and($recipe->categories->first())->toBeInstanceOf(Category::class);
});

test('recipe can have multiple ingredients with pivot data', function () {
    $recipe = Recipe::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $recipe->ingredients()->attach($ingredient, [
        'amount' => 2.5,
        'unit' => 'tbsp',
    ]);

    expect($recipe->ingredients)->toHaveCount(1)
        ->and($recipe->ingredients->first())->toBeInstanceOf(Ingredient::class)
        ->and($recipe->ingredients->first()->pivot->amount)->toBe(2.5)
        ->and($recipe->ingredients->first()->pivot->unit)->toBe('tbsp');
});

test('recipe can belong to multiple collections', function () {
    $recipe = Recipe::factory()->create();
    $collections = Collection::factory()->count(2)->create();

    $recipe->collections()->attach($collections);

    expect($recipe->collections)->toHaveCount(2)
        ->and($recipe->collections->first())->toBeInstanceOf(Collection::class);
});

test('recipe can have nutrition information', function () {
    $recipe = Recipe::factory()->create();
    $nutritionInfo = NutritionInformation::factory()->create([
        'recipe_id' => $recipe->id,
    ]);

    expect($recipe->nutritionInformation)->toBeInstanceOf(NutritionInformation::class)
        ->and($recipe->nutritionInformation->id)->toBe($nutritionInfo->id);
});

test('recipe generates slug from title', function () {
    $recipe = Recipe::factory()->create([
        'title' => 'Delicious Chocolate Cake',
    ]);

    expect($recipe->slug)->toBe('delicious-chocolate-cake');
});

test('recipe uses slug for route key name', function () {
    $recipe = new Recipe();

    expect($recipe->getRouteKeyName())->toBe('slug');
});

test('recipe slug options are configured correctly', function () {
    $recipe = new Recipe();
    $slugOptions = $recipe->getSlugOptions();

    expect($slugOptions)->toBeInstanceOf(SlugOptions::class);

    // Create a recipe with a title and verify the slug is generated correctly
    $recipe = Recipe::factory()->create([
        'title' => 'Test Recipe Title'
    ]);

    expect($recipe->slug)->toBe('test-recipe-title');
});

test('recipe has correct casts', function () {
    $recipe = new Recipe();
    $casts = $recipe->getCasts();

    expect($casts)->toBeArray()
        ->and($casts)->toHaveKeys([
            'cooking_time',
            'prep_time',
            'servings',
            'images',
            'is_public',
        ])
        ->and($casts['cooking_time'])->toBe('integer')
        ->and($casts['prep_time'])->toBe('integer')
        ->and($casts['servings'])->toBe('integer')
        ->and($casts['images'])->toBe('array')
        ->and($casts['is_public'])->toBe('boolean');
});

test('recipe has correct hidden attributes', function () {
    $recipe = new Recipe();

    expect($recipe->getHidden())->toContain('user_id')
        ->and($recipe->getHidden())->toContain('updated_at');
});

test('getMeasurementForIngredient returns null when ingredient not found', function () {
    $recipe = Recipe::factory()->create();
    $ingredient = Ingredient::factory()->create();

    expect($recipe->getMeasurementForIngredient($ingredient))->toBeNull();
});

test('getMeasurementForIngredient returns Measurement object with correct values', function () {
    $recipe = Recipe::factory()->create();
    $ingredient = Ingredient::factory()->create();

    $recipe->ingredients()->attach($ingredient, [
        'amount' => 2.5,
        'unit' => 'tbsp',
    ]);

    $measurement = $recipe->getMeasurementForIngredient($ingredient);

    expect($measurement)->toBeInstanceOf(Measurement::class)
        ->and($measurement->amount)->toBe(2.5)
        ->and($measurement->unit)->toBe(MeasurementUnit::TABLESPOON);
});

test('recipe can be favorited by users', function () {
    $recipe = Recipe::factory()->create();
    $users = User::factory()->count(3)->create();

    // Add recipe to favorites for all users
    foreach ($users as $user) {
        $user->favorites()->attach($recipe);
    }

    expect($recipe->favoritedBy)->toHaveCount(3)
        ->and($recipe->favoritedBy->first())->toBeInstanceOf(User::class);

    // Check the pivot table has the correct data
    expect($recipe->favoritedBy()->wherePivot('recipe_id', $recipe->id)->count())->toBe(3);
});

test('recipe is private by default', function () {
    $recipe = Recipe::factory()->create();

    expect($recipe->is_public)->toBeFalse();
});

test('recipe can be made public', function () {
    $recipe = Recipe::factory()->create(['is_public' => true]);

    expect($recipe->is_public)->toBeTrue();
});

test('isImported returns true when source_url is present', function () {
    $recipe = Recipe::factory()->create(['url' => 'https://example.com/recipe']);

    expect($recipe->isImported())->toBeTrue();
});

test('isImported returns false when source_url is null', function () {
    $recipe = Recipe::factory()->create(['url' => null]);

    expect($recipe->isImported())->toBeFalse();
});

test('isImported returns false when source_url is empty string', function () {
    $recipe = Recipe::factory()->create(['url' => '']);

    expect($recipe->isImported())->toBeFalse();
});
