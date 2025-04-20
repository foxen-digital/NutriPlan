<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeIndexService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeIndexServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecipeIndexService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new RecipeIndexService();
        $this->user = User::factory()->create();
    }

    public function test_it_returns_paginated_recipes(): void
    {
        // Arrange
        Recipe::factory()->count(5)->create(['is_public' => true]);
        
        // Act
        $result = $this->service->getRecipes($this->user);
        
        // Assert
        $this->assertEquals(5, $result->count());
        $this->assertNotNull($result->first()->user);
    }

    public function test_it_filters_by_category(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $matchingRecipe = Recipe::factory()->create(['is_public' => true]);
        $matchingRecipe->categories()->attach($category);
        
        Recipe::factory()->count(3)->create(['is_public' => true]);
        
        // Act
        $result = $this->service->getRecipes($this->user, ['category' => $category->id]);
        
        // Assert
        $this->assertEquals(1, $result->count());
        $this->assertEquals($matchingRecipe->id, $result->first()->id);
    }

    public function test_it_filters_by_user_own_recipes(): void
    {
        // Arrange
        Recipe::factory()->count(3)->create(['is_public' => true]);
        Recipe::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_public' => false
        ]);
        
        // Act
        $result = $this->service->getRecipes($this->user, ['show_mine' => true]);
        
        // Assert
        $this->assertEquals(2, $result->count());
        $this->assertEquals($this->user->id, $result->first()->user_id);
    }

    public function test_it_shows_public_recipes_and_user_own_recipes(): void
    {
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
        $this->assertEquals(5, $result->count());
    }

    public function test_it_adds_is_favorited_flag(): void
    {
        // Arrange
        $recipe = Recipe::factory()->create(['is_public' => true]);
        $this->user->favorites()->attach($recipe);
        
        // Act
        $result = $this->service->getRecipes($this->user);
        
        // Assert
        $this->assertTrue($result->first()->is_favorited);
    }
} 