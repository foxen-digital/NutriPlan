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

test('it filters by search term in name', function () {
    // Arrange
    Recipe::factory()->create(['title' => 'Amazing Chicken Soup', 'is_public' => true]);
    Recipe::factory()->create(['title' => 'Spicy Beef Stir-fry', 'is_public' => true]);
    Recipe::factory()->create(['title' => 'Creamy Tomato Soup', 'is_public' => true]);

    // Act
    $result = $this->service->getRecipes($this->user, ['search_term' => 'Soup', 'search_mode' => 'name_description']);

    // Assert
    expect($result->count())->toBe(2);
    expect($result->pluck('title')->all())->toEqual([
        'Amazing Chicken Soup',
        'Creamy Tomato Soup',
    ]);
});

test('it filters by search term in description', function () {
    // Arrange
    Recipe::factory()->create(['title' => 'Recipe A', 'description' => 'A delicious apple pie', 'is_public' => true]);
    Recipe::factory()->create(['title' => 'Recipe B', 'description' => 'Simple banana bread', 'is_public' => true]);
    Recipe::factory()->create(['title' => 'Recipe C', 'description' => 'Another tasty apple crumble', 'is_public' => true]);

    // Act
    $result = $this->service->getRecipes($this->user, ['search_term' => 'apple', 'search_mode' => 'name_description']);

    // Assert
    expect($result->count())->toBe(2);
    expect($result->pluck('title')->all())->toEqual(['Recipe A', 'Recipe C']);
});

test('it filters by search term in ingredients', function () {
    // Arrange
    $ingredient1 = \App\Models\Ingredient::factory()->create(['name' => 'Chicken Breast']);
    $ingredient2 = \App\Models\Ingredient::factory()->create(['name' => 'Garlic Powder']);
    $ingredient3 = \App\Models\Ingredient::factory()->create(['name' => 'Olive Oil']);

    $recipe1 = Recipe::factory()->create(['title' => 'Chicken Dish', 'is_public' => true]);
    $recipe1->ingredients()->attach($ingredient1, ['amount' => '1', 'unit' => 'piece']);
    $recipe1->ingredients()->attach($ingredient2, ['amount' => '1', 'unit' => 'tsp']);

    $recipe2 = Recipe::factory()->create(['title' => 'Garlic Bread', 'is_public' => true]);
    $recipe2->ingredients()->attach($ingredient2, ['amount' => '2', 'unit' => 'tsp']);
    $recipe2->ingredients()->attach($ingredient3, ['amount' => '2', 'unit' => 'tbsp']);

    $recipe3 = Recipe::factory()->create(['title' => 'Salad', 'is_public' => true]);
    $recipe3->ingredients()->attach($ingredient3, ['amount' => '1', 'unit' => 'tbsp']);

    // Act
    $result = $this->service->getRecipes($this->user, ['search_term' => 'Garlic', 'search_mode' => 'ingredient']);

    // Assert
    expect($result->count())->toBe(2);
    expect($result->pluck('title')->all())->toEqual(['Chicken Dish', 'Garlic Bread']);
});

test('search is case-insensitive', function (string $searchTerm) {
    // Arrange
    Recipe::factory()->create(['title' => 'Amazing Chicken Soup', 'is_public' => true]);
    Recipe::factory()->create(['title' => 'Spicy Beef Stir-fry', 'is_public' => true]);

    // Act
    $result = $this->service->getRecipes($this->user, ['search_term' => $searchTerm, 'search_mode' => 'name_description']);

    // Assert
    expect($result->count())->toBe(1);
    expect($result->first()->title)->toBe('Amazing Chicken Soup');
})->with(['chicken', 'CHICKEN', 'cHicKeN']);

test('search filter respects other filters like show_mine', function () {
    // Arrange
    $otherUser = User::factory()->create();
    Recipe::factory()->create(['user_id' => $this->user->id, 'title' => 'My Chicken Soup', 'is_public' => true]);
    Recipe::factory()->create(['user_id' => $otherUser->id, 'title' => 'Their Chicken Soup', 'is_public' => true]);
    Recipe::factory()->create(['user_id' => $this->user->id, 'title' => 'My Beef Stew', 'is_public' => true]);

    // Act: Search for 'Soup' but only show mine
    $result = $this->service->getRecipes($this->user, [
        'search_term' => 'Soup',
        'search_mode' => 'name_description',
        'show_mine' => true,
    ]);

    // Assert
    expect($result->count())->toBe(1);
    expect($result->first()->title)->toBe('My Chicken Soup');
    expect($result->first()->user_id)->toBe($this->user->id);
}); 