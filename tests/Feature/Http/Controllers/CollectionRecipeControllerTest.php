<?php

declare(strict_types=1);

use App\Models\Collection;
use App\Models\Recipe;
use App\Models\User;

test('store adds a recipe to collection', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->post(route('collections.add-recipe'), [
            'collection_id' => $collection->id,
            'recipe_id' => $recipe->id,
        ]);

    $response->assertSessionHas('success');

    expect(
        $collection->recipes()
            ->where('recipe_id', $recipe->id)
            ->exists()
    )->toBeTrue();
});

test('destroy removes a recipe from collection', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $user->id,
    ]);
    $recipe = Recipe::factory()->create([
        'user_id' => $user->id,
    ]);

    $collection->recipes()->attach($recipe->id);

    $response = $this->actingAs($user)
        ->delete(route('collections.remove-recipe', [
            'collection' => $collection,
            'recipe' => $recipe,
        ]));

    $response->assertSessionHas('success');

    expect(
        $collection->recipes()
            ->where('recipe_id', $recipe->id)
            ->exists()
    )->toBeFalse();
});

test('store returns 403 when collection_id is missing', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('collections.add-recipe'), [
            // 'collection_id' => missing
            'recipe_id' => $recipe->id,
        ]);

    $response->assertForbidden(); // Correct: Authorize fails first because find(null) is null
});

test('store fails validation when recipe_id is missing', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('collections.add-recipe'), [
            'collection_id' => $collection->id,
            // 'recipe_id' => missing
        ]);

    $response->assertSessionHasErrors('recipe_id');
    $response->assertStatus(302);
});

test('store returns 403 when collection_id does not exist', function () {
    $user = User::factory()->create();
    $recipe = Recipe::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('collections.add-recipe'), [
            'collection_id' => 999, // Non-existent ID
            'recipe_id' => $recipe->id,
        ]);

    $response->assertForbidden();
});

test('store fails validation when recipe_id does not exist', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('collections.add-recipe'), [
            'collection_id' => $collection->id,
            'recipe_id' => 999, // Non-existent ID
        ]);

    $response->assertSessionHasErrors('recipe_id');
    $response->assertStatus(302);
});

test('user cannot add recipe to others collections (Form Request handles)', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $recipe = Recipe::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $collection->recipes()->attach($recipe->id);

    $response = $this->actingAs($user)
        ->delete(route('collections.remove-recipe', [
            'collection' => $collection,
            'recipe' => $recipe,
        ]));

    $response->assertForbidden();
});
