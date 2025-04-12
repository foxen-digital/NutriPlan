<?php

declare(strict_types=1);

use App\Models\Ingredient;
use App\Models\ShoppingListItem;
use App\Models\User;
use App\Models\ShoppingList;

test('unauthenticated user cannot search items', function () {
    $response = $this->getJson('/api/item-search?query=milk');

    $response->assertStatus(401);
});

test('returns empty array for short queries', function () {
    $user = User::factory()->create();

    // Create test item
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $user->id,
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Milk',
    ]);

    // Single character search should return empty
    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=m');

    $response->assertStatus(200)
        ->assertExactJson([]);

    // Empty search should return empty
    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=');

    $response->assertStatus(200)
        ->assertExactJson([]);
});

test('returns matching shopping list item names', function () {
    $user = User::factory()->create();

    // Create shopping list with items
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $user->id,
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Milk',
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Whole Milk',
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Bread',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=milk');

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment(['Milk'])
        ->assertJsonFragment(['Whole Milk']);
});

test('returns matching ingredient names', function () {
    $user = User::factory()->create();

    // Create ingredients
    Ingredient::factory()->create([
        'name' => 'Cinnamon',
    ]);

    Ingredient::factory()->create([
        'name' => 'Ground Cinnamon',
    ]);

    Ingredient::factory()->create([
        'name' => 'Sugar',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=cinnamon');

    $response->assertStatus(200)
        ->assertJsonCount(2)
        ->assertJsonFragment(['Cinnamon'])
        ->assertJsonFragment(['Ground Cinnamon']);
});

test('returns combined unique results', function () {
    $user = User::factory()->create();

    // Create shopping list with items
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $user->id,
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Butter',
    ]);

    ShoppingListItem::factory()->create([
        'shopping_list_id' => $shoppingList->id,
        'name' => 'Peanut Butter',
    ]);

    // Create ingredients with overlapping name
    Ingredient::factory()->create([
        'name' => 'Butter',
    ]);

    Ingredient::factory()->create([
        'name' => 'Almond Butter',
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=butter');

    $response->assertStatus(200)
        ->assertJsonCount(3) // Should return 3 unique items, not 4
        ->assertJsonFragment(['Butter'])
        ->assertJsonFragment(['Peanut Butter'])
        ->assertJsonFragment(['Almond Butter']);
});

test('limits results to 5 from each source', function () {
    $user = User::factory()->create();

    // Create shopping list with many items
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $user->id,
    ]);

    $appleItems = [
        'Apple',
        'Green Apple',
        'Red Apple',
        'Apple Juice',
        'Apple Cider',
        'Apple Pie Filling',
        'Apple Sauce',
    ];

    foreach ($appleItems as $name) {
        ShoppingListItem::factory()->create([
            'shopping_list_id' => $shoppingList->id,
            'name' => $name,
        ]);
    }

    // Create many apple ingredients
    $appleIngredients = [
        'Apple Extract',
        'Apple Vinegar',
        'Dried Apple',
        'Apple Butter',
        'Apple Jelly',
        'Apple Syrup',
        'Apple Puree',
    ];

    foreach ($appleIngredients as $name) {
        Ingredient::factory()->create([
            'name' => $name,
        ]);
    }

    $response = $this->actingAs($user)
        ->getJson('/api/item-search?query=apple');

    $response->assertStatus(200)
        ->assertJsonCount(10); // Should return 10 (5 from each source) not 14
});
