<?php

declare(strict_types=1);

use App\Enums\MeasurementUnit;
use App\Jobs\NormalizeRecipeInstructionsJob;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\NutritionInformation;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can view recipe list', function () {
    $recipes = Recipe::factory()
        ->count(3)
        ->for($this->user)
        ->has(Category::factory()->count(2))
        ->create()
        ->each(function (Recipe $recipe) {
            $recipe->ingredients()->attach(
                Ingredient::factory()
                    ->count(3)
                    ->create()
                    ->mapWithKeys(fn ($ingredient) => [
                        $ingredient->id => [
                            'amount' => fake()->randomFloat(2, 0.25, 10),
                            'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                        ],
                    ])
                    ->toArray()
            );
        });

    $response = actingAs($this->user)
        ->get(route('recipes.index'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 3)
        ->has(
            'recipes.data.0',
            fn (AssertableInertia $page) => $page
            ->has('id')
            ->has('title')
            ->has('description')
            ->has('prep_time')
            ->has('cooking_time')
            ->has('servings')
            ->has('images')
            ->has('categories')
            ->has('user')
            ->has('instructions')
            ->has('url')
            ->has('author')
            ->has('slug')
            ->has('created_at')
            ->has('is_favorited')
            ->has('is_public')
        )
    );
});

test('guest cannot view recipe list', function () {
    Recipe::factory()->count(3)->create();

    $response = get(route('recipes.index'));

    $response->assertRedirect(route('login'));
});

test('recipe list is paginated', function () {
    Recipe::factory()
        ->count(15)
        ->for($this->user)
        ->create();

    $response = actingAs($this->user)
        ->get(route('recipes.index'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 12) // Default pagination is 12 items
        ->has('recipes.links')
        ->has('recipes.current_page')
        ->has('recipes.next_page_url')
        ->has('recipes.path')
        ->has('recipes.per_page')
        ->has('recipes.prev_page_url')
        ->has('recipes.to')
        ->has('recipes.total')
    );
});

test('recipes are ordered by latest first', function () {
    $oldRecipe = Recipe::factory()
        ->for($this->user)
        ->create(['created_at' => now()->subDays(2)]);
    $newRecipe = Recipe::factory()
        ->for($this->user)
        ->create(['created_at' => now()]);

    $response = actingAs($this->user)
        ->get(route('recipes.index'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->where('recipes.data.0.id', $newRecipe->id)
        ->where('recipes.data.1.id', $oldRecipe->id)
    );
});

test('recipe list includes categories and user', function () {
    $recipe = Recipe::factory()
        ->for($this->user)
        ->has(Category::factory()->count(2))
        ->create();

    $response = actingAs($this->user)
        ->get(route('recipes.index'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data.0.categories', 2)
        ->has(
            'recipes.data.0.user',
            fn (AssertableInertia $page) => $page
            ->has('id')
            ->has('name')
            ->has('slug')
        )
    );
});

test('guest cannot create recipe', function () {
    $response = get(route('recipes.create'));

    $response->assertRedirect(route('login'));
});

test('user can create recipe', function () {
    $response = actingAs($this->user)
        ->get(route('recipes.create'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Create')
    );
});

test('user can store recipe', function () {
    Queue::fake();

    $categories = Category::factory(2)->create();
    $ingredients = Ingredient::factory(3)->create();

    $response = actingAs($this->user)
        ->post(route('recipes.store'), [
            'title' => 'Test Recipe',
            'description' => 'Test Description',
            'instructions' => 'Test Instructions',
            'prep_time' => 30,
            'cooking_time' => 45,
            'servings' => 4,
            'categories' => $categories->pluck('id')->toArray(),
            'ingredients' => $ingredients->map(fn ($ingredient) => [
                'ingredient_id' => $ingredient->id,
                'amount' => 2.5,
                'unit' => MeasurementUnit::CUP->value,
            ])->toArray(),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(Recipe::where('title', 'Test Recipe')->exists())->toBeTrue();

    $recipe = Recipe::where('title', 'Test Recipe')->first();
    expect($recipe->instructions)->toBe('Test Instructions');
    expect($recipe->categories)->toHaveCount(2);
    expect($recipe->ingredients)->toHaveCount(3);

    Queue::assertPushed(NormalizeRecipeInstructionsJob::class, function (NormalizeRecipeInstructionsJob $job) use ($recipe) {
        return $job->recipeId === $recipe->id &&
               $job->userId === $this->user->id;
    });
});

test('user can store recipe with images', function () {
    Queue::fake();
    Storage::fake('public');

    // A 1x1 transparent PNG; UploadedFile::fake()->image() needs GD, which is not installed.
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

    $response = actingAs($this->user)
        ->post(route('recipes.store'), [
            'title' => 'Recipe With Images',
            'instructions' => 'Test Instructions',
            'prep_time' => 30,
            'cooking_time' => 45,
            'servings' => 4,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 2.5, 'unit' => MeasurementUnit::CUP->value],
            ],
            'images' => [
                UploadedFile::fake()->createWithContent('one.png', $png),
                UploadedFile::fake()->createWithContent('two.png', $png),
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $recipe = Recipe::where('title', 'Recipe With Images')->first();

    expect($recipe->images)->toHaveCount(2);
    expect($recipe->images[0])->toStartWith('/storage/recipes/');
    expect($recipe->images[1])->toStartWith('/storage/recipes/');

    Storage::disk('public')->assertExists([
        Str::after($recipe->images[0], '/storage/'),
        Str::after($recipe->images[1], '/storage/'),
    ]);
});

test('user can store recipe with nutrition information', function () {
    Queue::fake();

    $response = actingAs($this->user)
        ->post(route('recipes.store'), [
            'title' => 'Recipe With Nutrition',
            'instructions' => 'Test Instructions',
            'prep_time' => 30,
            'cooking_time' => 45,
            'servings' => 4,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 2.5, 'unit' => MeasurementUnit::CUP->value],
            ],
            'nutrition_information' => [
                'calories' => '240 calories',
                'protein_content' => '4g',
                'sodium_content' => '200mg',
                'hacker_field' => 'should be ignored',
            ],
        ]);

    $response->assertRedirect();

    $recipe = Recipe::where('title', 'Recipe With Nutrition')->first();

    expect($recipe->nutritionInformation)->not->toBeNull();
    expect($recipe->nutritionInformation->calories)->toBe('240 calories');
    expect($recipe->nutritionInformation->protein_content)->toBe('4g');
    expect($recipe->nutritionInformation->sodium_content)->toBe('200mg');
});

test('storing recipe without nutrition information creates no record', function () {
    Queue::fake();

    $response = actingAs($this->user)
        ->post(route('recipes.store'), [
            'title' => 'Recipe Without Nutrition',
            'instructions' => 'Test Instructions',
            'prep_time' => 30,
            'cooking_time' => 45,
            'servings' => 4,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 2.5, 'unit' => MeasurementUnit::CUP->value],
            ],
        ]);

    $response->assertRedirect();

    $recipe = Recipe::where('title', 'Recipe Without Nutrition')->first();

    expect($recipe->nutritionInformation)->toBeNull();
});

test('guest cannot view recipe', function () {
    $recipe = Recipe::factory()->create();

    $response = get(route('recipes.show', $recipe));

    $response->assertRedirect(route('login'));
});

test('user can view recipe', function () {
    $recipe = Recipe::factory()
        ->for($this->user)
        ->has(Category::factory()->count(2))
        ->create();

    $recipe->ingredients()->attach(
        Ingredient::factory()
            ->count(3)
            ->create()
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->id => [
                    'amount' => fake()->randomFloat(2, 0.25, 10),
                    'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                ],
            ])
            ->toArray()
    );

    $response = actingAs($this->user)
        ->get(route('recipes.show', $recipe));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Show')
        ->has('recipe')
        ->where('recipe.id', $recipe->id)
        ->has('recipe.categories', 2)
        ->has('recipe.ingredients', 3)
    );
});

test('guest cannot edit recipe', function () {
    $recipe = Recipe::factory()->create();

    $response = get(route('recipes.edit', $recipe));

    $response->assertRedirect(route('login'));
});

test('user can edit own recipe', function () {
    $recipe = Recipe::factory()
        ->for($this->user)
        ->has(Category::factory()->count(2))
        ->create();

    $recipe->ingredients()->attach(
        Ingredient::factory()
            ->count(3)
            ->create()
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->id => [
                    'amount' => fake()->randomFloat(2, 0.25, 10),
                    'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                ],
            ])
            ->toArray()
    );

    $response = actingAs($this->user)
        ->get(route('recipes.edit', $recipe));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Edit')
        ->has('recipe')
        ->where('recipe.id', $recipe->id)
        ->has('recipe.categories', 2)
        ->has('recipe.ingredients', 3)
    );
});

test('user cannot edit others recipe', function () {
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()
        ->for($otherUser)
        ->create();

    $response = actingAs($this->user)
        ->get(route('recipes.edit', $recipe));

    $response->assertForbidden();
});

test('user can update own recipe', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->create();

    $categories = Category::factory(2)->create();

    $recipe->ingredients()->attach(
        Ingredient::factory()
            ->count(3)
            ->create()
            ->mapWithKeys(fn ($ingredient) => [
                $ingredient->id => [
                    'amount' => fake()->randomFloat(2, 0.25, 10),
                    'unit' => fake()->randomElement(array_column(MeasurementUnit::cases(), 'value')),
                ],
            ])
            ->toArray()
    );

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'description' => 'Updated Description',
            'instructions' => 'Updated Instructions',
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'categories' => $categories->pluck('id')->toArray(),
            'ingredients' => $recipe->ingredients->map(fn ($ingredient) => [
                'ingredient_id' => $ingredient->id,
                'amount' => 3.5,
                'unit' => MeasurementUnit::TABLESPOON->value,
            ])->toArray(),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $recipe->refresh();
    expect($recipe->title)->toBe('Updated Recipe');
    expect($recipe->description)->toBe('Updated Description');
    expect($recipe->instructions)->toBe('Updated Instructions');
    expect($recipe->prep_time)->toBe(45);
    expect($recipe->cooking_time)->toBe(60);
    expect($recipe->servings)->toBe(6);
    expect($recipe->categories)->toHaveCount(2);
    expect($recipe->ingredients)->toHaveCount(3);

    Queue::assertPushed(NormalizeRecipeInstructionsJob::class, function (NormalizeRecipeInstructionsJob $job) use ($recipe) {
        return $job->recipeId === $recipe->id &&
               $job->userId === $this->user->id;
    });
});

test('updating recipe replaces images with kept and newly uploaded images', function () {
    Queue::fake();
    Storage::fake('public');

    // A 1x1 transparent PNG; UploadedFile::fake()->image() needs GD, which is not installed.
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

    $recipe = Recipe::factory()
        ->for($this->user)
        ->create(['images' => ['/storage/recipes/existing.png', '/storage/recipes/removed.png']]);

    Storage::disk('public')->put('recipes/existing.png', 'existing');
    Storage::disk('public')->put('recipes/removed.png', 'removed');

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => 'Updated Instructions',
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'existing_images' => ['/storage/recipes/existing.png'],
            'images' => [UploadedFile::fake()->createWithContent('new.png', $png)],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->images)->toHaveCount(2);
    expect($recipe->images[0])->toBe('/storage/recipes/existing.png');
    expect($recipe->images[1])->toStartWith('/storage/recipes/');

    Storage::disk('public')->assertExists('recipes/existing.png');
    Storage::disk('public')->assertMissing('recipes/removed.png');
    Storage::disk('public')->assertExists(Str::after($recipe->images[1], '/storage/'));

    // Instructions changed in the same edit as the image sync, so the job must still be queued.
    Queue::assertPushed(NormalizeRecipeInstructionsJob::class, function (NormalizeRecipeInstructionsJob $job) use ($recipe) {
        return $job->recipeId === $recipe->id &&
               $job->userId === $this->user->id;
    });
});

test('updating recipe with unchanged existing images keeps them', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->create(['images' => ['/storage/recipes/existing.png']]);

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => $recipe->instructions,
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'existing_images' => ['/storage/recipes/existing.png'],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->images)->toBe(['/storage/recipes/existing.png']);
    Queue::assertNothingPushed();
});

test('updating recipe ignores existing image urls not attached to the recipe', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->create(['images' => ['/storage/recipes/existing.png']]);

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => $recipe->instructions,
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'existing_images' => [
                '/storage/recipes/existing.png',
                '/storage/recipes/someone-elses-image.png',
                'https://evil.example/image.png',
            ],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->images)->toBe(['/storage/recipes/existing.png']);
});

test('updating recipe replaces nutrition information and clears emptied fields', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->has(NutritionInformation::factory()->state([
            'calories' => '100 calories',
            'fat_content' => '2g',
        ]))
        ->create();

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => $recipe->instructions,
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'nutrition_information' => [
                'calories' => '300 calories',
                'fat_content' => '',
            ],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->nutritionInformation->calories)->toBe('300 calories');
    expect($recipe->nutritionInformation->fat_content)->toBeNull();
});

test('updating recipe creates nutrition information when missing', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->create();

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => $recipe->instructions,
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'nutrition_information' => ['fiber_content' => '3g'],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->nutritionInformation)->not->toBeNull();
    expect($recipe->nutritionInformation->fiber_content)->toBe('3g');
});

test('updating recipe with empty nutrition information removes it', function () {
    Queue::fake();

    $recipe = Recipe::factory()
        ->for($this->user)
        ->has(NutritionInformation::factory()->state([
            'calories' => '100 calories',
        ]))
        ->create();

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'instructions' => $recipe->instructions,
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
            'ingredients' => [
                ['ingredient_id' => Ingredient::factory()->create()->id, 'amount' => 1, 'unit' => MeasurementUnit::CUP->value],
            ],
            'nutrition_information' => [
                'calories' => '',
                'protein_content' => '   ',
            ],
        ]);

    $response->assertRedirect();

    $recipe->refresh();

    expect($recipe->nutritionInformation)->toBeNull();
});

test('user cannot update others recipe', function () {
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()
        ->for($otherUser)
        ->create();

    $response = actingAs($this->user)
        ->put(route('recipes.update', $recipe), [
            'title' => 'Updated Recipe',
            'description' => 'Updated Description',
            'instructions' => 'Updated Instructions',
            'prep_time' => 45,
            'cooking_time' => 60,
            'servings' => 6,
        ]);

    $response->assertForbidden();
});

test('guest cannot delete recipe', function () {
    $recipe = Recipe::factory()->create();

    $response = delete(route('recipes.destroy', $recipe));

    $response->assertRedirect(route('login'));
});

test('user can delete own recipe', function () {
    $recipe = Recipe::factory()
        ->for($this->user)
        ->create();

    $response = actingAs($this->user)
        ->delete(route('recipes.destroy', $recipe));

    $response->assertRedirect(route('recipes.index'));
    $response->assertSessionHas('success');

    expect(Recipe::find($recipe->id))->toBeNull();
});

test('user cannot delete others recipe', function () {
    $otherUser = User::factory()->create();
    $recipe = Recipe::factory()
        ->for($otherUser)
        ->create();

    $response = actingAs($this->user)
        ->delete(route('recipes.destroy', $recipe));

    $response->assertForbidden();
});

test('user can delete own recipe with nutrition information', function () {
    $recipe = Recipe::factory()
        ->for($this->user)
        ->create();

    $nutrition = $recipe->nutritionInformation()->create([
        'calories' => '200 kcal',
        'protein_content' => '10g',
        'carbohydrate_content' => '30g',
        'fat_content' => '5g',
    ]);

    $response = actingAs($this->user)
        ->delete(route('recipes.destroy', $recipe));

    $response->assertRedirect(route('recipes.index'));
    $response->assertSessionHas('success');

    expect(Recipe::find($recipe->id))->toBeNull();
    expect(\App\Models\NutritionInformation::find($nutrition->id))->toBeNull();
});

test('recipe list only shows public recipes and user own recipes', function () {
    $otherUser = User::factory()->create();

    // User's own recipes (both public and private)
    $ownPublicRecipe = Recipe::factory()->for($this->user)->create(['is_public' => true]);
    $ownPrivateRecipe = Recipe::factory()->for($this->user)->create(['is_public' => false]);

    // Other user's recipes (one public, one private)
    $otherPublicRecipe = Recipe::factory()->for($otherUser)->create(['is_public' => true]);
    $otherPrivateRecipe = Recipe::factory()->for($otherUser)->create(['is_public' => false]);

    $response = actingAs($this->user)
        ->get(route('recipes.index'));

    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 3) // Should contain own public, own private, and other public recipes
        ->where('recipes.data.0.id', $ownPublicRecipe->id)
        ->where('recipes.data.1.id', $ownPrivateRecipe->id)
        ->where('recipes.data.2.id', $otherPublicRecipe->id)
    );
});

test('user can view other users public recipes', function () {
    $otherUser = User::factory()->create();
    $publicRecipe = Recipe::factory()
        ->for($otherUser)
        ->create(['is_public' => true]);

    $response = actingAs($this->user)
        ->get(route('recipes.show', $publicRecipe));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Show')
        ->where('recipe.id', $publicRecipe->id)
        ->where('recipe.is_public', true)
        ->where('isOwner', false)
        ->where('hideDetails', $publicRecipe->isImported())
    );
});

test('user cannot view other users private recipes', function () {
    $otherUser = User::factory()->create();
    $privateRecipe = Recipe::factory()
        ->for($otherUser)
        ->create(['is_public' => false]);

    $response = actingAs($this->user)
        ->get(route('recipes.show', $privateRecipe));

    $response->assertStatus(403);
});

test('imported public recipes hide ingredients and instructions from non-owners', function () {
    $otherUser = User::factory()->create();
    $importedRecipe = Recipe::factory()
        ->for($otherUser)
        ->create([
            'is_public' => true,
            'url' => 'https://example.com/recipe',
            'author' => 'Example Recipes'
        ]);

    $response = actingAs($this->user)
        ->get(route('recipes.show', $importedRecipe));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Show')
        ->where('recipe.id', $importedRecipe->id)
        ->where('recipe.is_public', true)
        ->where('isOwner', false)
        ->where('hideDetails', true)
    );
});

test('owner can view full details of their imported recipe', function () {
    $importedRecipe = Recipe::factory()
        ->for($this->user)
        ->create([
            'is_public' => true,
            'url' => 'https://example.com/recipe',
            'author' => 'Example Recipes'
        ]);

    $response = actingAs($this->user)
        ->get(route('recipes.show', $importedRecipe));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Show')
        ->where('recipe.id', $importedRecipe->id)
        ->where('recipe.is_public', true)
        ->where('isOwner', true)
        ->where('hideDetails', false)
    );
});

test('recipe index filters by search term in name/description', function () {
    $recipe1 = Recipe::factory()->create(['title' => 'Special Chicken Soup', 'is_public' => true]);
    $recipe2 = Recipe::factory()->create(['description' => 'A lovely chicken pie', 'is_public' => true]);
    $recipe3 = Recipe::factory()->create(['title' => 'Beef Stew', 'is_public' => true]);

    $response = actingAs($this->user)
        ->get(route('recipes.index', ['search_term' => 'chicken', 'search_mode' => 'name_description']));

    $response->assertOk();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 2)
        ->where('recipes.data.0.id', $recipe1->id)
        ->where('recipes.data.1.id', $recipe2->id)
        ->where('filter.search_term', 'chicken')
        ->where('filter.search_mode', 'name_description')
    );
});

test('recipe index filters by search term in ingredients', function () {
    $ingredient1 = Ingredient::factory()->create(['name' => 'Chicken Breast']);
    $ingredient2 = Ingredient::factory()->create(['name' => 'Carrot']);

    $recipe1 = Recipe::factory()->create(['title' => 'Chicken and Carrot Dish', 'is_public' => true]);
    $recipe1->ingredients()->attach($ingredient1, ['amount' => '1', 'unit' => 'piece']);
    $recipe1->ingredients()->attach($ingredient2, ['amount' => '2', 'unit' => 'piece']);

    $recipe2 = Recipe::factory()->create(['title' => 'Carrot Cake', 'is_public' => true]);
    $recipe2->ingredients()->attach($ingredient2, ['amount' => '3', 'unit' => 'large']);

    $recipe3 = Recipe::factory()->create(['title' => 'Simple Salad', 'is_public' => true]);

    $response = actingAs($this->user)
        ->get(route('recipes.index', ['search_term' => 'carrot', 'search_mode' => 'ingredient']));

    $response->assertOk();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 2)
        ->where('recipes.data.0.id', $recipe1->id)
        ->where('recipes.data.1.id', $recipe2->id)
        ->where('filter.search_term', 'carrot')
        ->where('filter.search_mode', 'ingredient')
    );
});

test('recipe index pagination links include search parameters', function () {
    Recipe::factory()->count(15)->create(['title' => 'Searchable Recipe', 'is_public' => true]);

    $response = actingAs($this->user)
        ->get(route('recipes.index', ['search_term' => 'Searchable', 'search_mode' => 'name_description']));

    $response->assertOk();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 12)
        ->has('recipes.next_page_url')
        ->where(
            'recipes.next_page_url',
            fn (string $url) =>
            str_contains($url, 'page=2') &&
            str_contains($url, 'search_term=Searchable') &&
            str_contains($url, 'search_mode=name_description')
        )
    );
});

test('recipe index returns correct filter props', function () {
    actingAs($this->user)
        ->get(route('recipes.index', [
            'category' => 1,
            'show_mine' => true,
            'search_term' => 'test search',
            'search_mode' => 'ingredient'
        ]))
        ->assertInertia(
            fn (AssertableInertia $page) => $page
            ->where('filter', [
                'category' => '1',
                'show_mine' => '1',
                'search_term' => 'test search',
                'search_mode' => 'ingredient'
            ])
        );
});

test('empty search term is treated as null', function () {
    // Arrange
    $recipe1 = Recipe::factory()->create(['title' => 'Recipe One', 'is_public' => true]);
    $recipe2 = Recipe::factory()->create(['title' => 'Recipe Two', 'is_public' => true]);

    // Act: Perform GET request with empty search_term
    $response = actingAs($this->user)
        ->get(route('recipes.index', ['search_term' => '']));

    // Assert: All recipes should be returned, and filter prop should reflect null search_term
    $response->assertOk();
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Index')
        ->has('recipes.data', 2)
        ->where('filter.search_term', null)
        ->etc()
    );
});
