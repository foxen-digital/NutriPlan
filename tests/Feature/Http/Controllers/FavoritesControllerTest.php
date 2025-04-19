<?php

declare(strict_types=1);

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('authenticated user can view their favorite recipes', function () {
    $user = User::factory()->create();
    $recipes = Recipe::factory()->count(3)->create();

    // Add two recipes to favorites
    $user->favorites()->attach([$recipes[0]->id, $recipes[1]->id]);

    $response = $this->actingAs($user)
        ->get(route('favorites.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Favorites')
        ->has('favorites.data', 2)
        ->where('favorites.data.0.id', $recipes[0]->id)
        ->where('favorites.data.1.id', $recipes[1]->id)
        ->where('favorites.data.0.is_favorited', true)
        ->where('favorites.data.1.is_favorited', true)
    );
});

test('authenticated user sees empty favorites page when they have no favorites', function () {
    $user = User::factory()->create();
    Recipe::factory()->count(3)->create(); // Create recipes but don't favorite them

    $response = $this->actingAs($user)
        ->get(route('favorites.index'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn (AssertableInertia $page) => $page
        ->component('Recipes/Favorites')
        ->has('favorites.data', 0)
    );
});

test('guest cannot view favorites page', function () {
    $response = $this->get(route('favorites.index'));

    $response->assertRedirect(route('login'));
});
