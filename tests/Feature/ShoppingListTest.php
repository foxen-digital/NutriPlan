<?php

declare(strict_types=1);

use App\Models\ShoppingList;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('user can view shopping lists index page', function () {
    // Arrange
    ShoppingList::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    // Act
    $response = $this->get(route('shopping-lists.index'));

    // Assert
    $response->assertStatus(200);
    $response->assertInertia(
        fn (Assert $page) => $page
        ->component('ShoppingLists/Index')
        ->has('shoppingLists')
    );
});

test('user can create a shopping list', function () {
    // Arrange
    $listData = [
        'name' => 'My Test Shopping List',
    ];

    // Act
    $response = $this->post(route('shopping-lists.store'), $listData);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('shopping_lists', [
        'name' => 'My Test Shopping List',
        'user_id' => $this->user->id,
    ]);
});

test('user can view a shopping list', function () {
    // Arrange
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Act
    $response = $this->get(route('shopping-lists.show', $shoppingList));

    // Assert
    $response->assertStatus(200);
    $response->assertInertia(
        fn (Assert $page) => $page
        ->component('ShoppingLists/Show')
        ->has('shoppingList')
    );
});

test('user can update a shopping list', function () {
    // Arrange
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Act
    $response = $this->put(route('shopping-lists.update', $shoppingList), [
        'name' => 'Updated Shopping List Name',
    ]);

    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('shopping_lists', [
        'id' => $shoppingList->id,
        'name' => 'Updated Shopping List Name',
    ]);
});

test('user can delete a shopping list', function () {
    // Arrange
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Act
    $response = $this->delete(route('shopping-lists.destroy', $shoppingList));

    // Assert
    $response->assertRedirect(route('shopping-lists.index'));
    $this->assertDatabaseMissing('shopping_lists', [
        'id' => $shoppingList->id,
    ]);
});

test('user cannot view another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // Act
    $response = $this->get(route('shopping-lists.show', $shoppingList));

    // Assert
    $response->assertForbidden();
});

test('user cannot update another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // Act
    $response = $this->put(route('shopping-lists.update', $shoppingList), [
        'name' => 'Updated Shopping List Name',
    ]);

    // Assert
    $response->assertForbidden();
});

test('user cannot delete another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $shoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // Act
    $response = $this->delete(route('shopping-lists.destroy', $shoppingList));

    // Assert
    $response->assertForbidden();
});
