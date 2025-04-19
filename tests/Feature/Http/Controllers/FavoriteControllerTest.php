<?php

declare(strict_types=1);

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can favorite a recipe', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('recipes.favorite', $recipe));

    $response->assertStatus(200)
        ->assertJson(['favorited' => true]);

    $this->assertDatabaseHas('recipe_user_favorites', [
        'user_id' => $user->id,
        'recipe_id' => $recipe->id,
    ]);
});

test('user can unfavorite a recipe', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create();

    // First favorite the recipe
    $user->favorites()->attach($recipe);

    $response = $this->actingAs($user)
        ->post(route('recipes.favorite', $recipe));

    $response->assertStatus(200)
        ->assertJson(['favorited' => false]);

    $this->assertDatabaseMissing('recipe_user_favorites', [
        'user_id' => $user->id,
        'recipe_id' => $recipe->id,
    ]);
});

test('guest cannot favorite a recipe', function () {
    $recipe = Recipe::factory()->create();

    $response = $this->post(route('recipes.favorite', $recipe));

    $response->assertRedirect(route('login'));

    $this->assertDatabaseCount('recipe_user_favorites', 0);
});
