<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new RecipeIndexService();
    $this->user = User::factory()->create();
});

test('it returns paginated recipes', function () {
    // Arrange
    Recipe::factory()->count(5)->create(['is_public' => true]);
    
    // Act
    $result = $this->service->getRecipes($this->user);
    
    // Assert
    expect($result->count())->toBe(5);
    expect($result->first()->user)->not->toBeNull();
});

test('it filters by category', function () {
    // Arrange
    $category = Category::factory()->create();
    $matchingRecipe = Recipe::factory()->create(['is_public' => true]);
    $matchingRecipe->categories()->attach($category);
    
    Recipe::factory()->count(3)->create(['is_public' => true]);
    
    // Act
    $result = $this->service->getRecipes($this->user, ['category' => $category->id]);
    
    // Assert
    expect($result->count())->toBe(1);
    expect($result->first()->id)->toBe($matchingRecipe->id);
});

test('it filters by user own recipes', function () {
    // Arrange
    Recipe::factory()->count(3)->create(['is_public' => true]);
    Recipe::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'is_public' => false
    ]);
    
    // Act
    $result = $this->service->getRecipes($this->user, ['show_mine' => true]);
    
    // Assert
    expect($result->count())->toBe(2);
    expect($result->first()->user_id)->toBe($this->user->id);
});

test('it shows public recipes and user own recipes', function () {
    // Arrange
    Recipe::factory()->count(2)->create(['is_public' => true]);
    Recipe::factory()->count(3)->create([
        'user_id' => $this->user->id,
        'is_public' => false
    ]);
    Recipe::factory()->count(1)->create(['is_public' => false]);
    
    // Act
    $result = $this->service->getRecipes($this->user);
    
    // Assert
    expect($result->count())->toBe(5);
});

test('it adds is favorited flag', function () {
    // Arrange
    $recipe = Recipe::factory()->create(['is_public' => true]);
    $this->user->favorites()->attach($recipe);
    
    // Act
    $result = $this->service->getRecipes($this->user);
    
    // Assert
    expect($result->first()->is_favorited)->toBeTrue();
}); 