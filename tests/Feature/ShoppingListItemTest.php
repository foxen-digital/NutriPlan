<?php

declare(strict_types=1);

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    $this->shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('user can add an item to their shopping list', function () {
    // Arrange
    $itemData = [
        'name' => 'Test Item',
        'quantity' => 2,
        'unit' => 'kg',
        'category' => 'Test Category',
    ];

    // Act
    $response = $this->post(route('shopping-lists.items.store', $this->shoppingList), $itemData);

    // Assert
    $response->assertRedirect(route('shopping-lists.show', $this->shoppingList));
    $this->assertDatabaseHas('shopping_list_items', [
        'shopping_list_id' => $this->shoppingList->id,
        'name' => 'Test Item',
        'quantity' => 2,
        'unit' => 'kg',
        'category' => 'Test Category',
        'is_custom' => true,
    ]);
});

test('user can update an item in their shopping list', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'name' => 'Original Item',
        'quantity' => 1,
        'unit' => 'pcs',
        'category' => 'Original Category',
    ]);

    // Act
    $response = $this->put(route('shopping-lists.items.update', [$this->shoppingList, $item]), [
        'name' => 'Updated Item',
        'quantity' => 3,
        'unit' => 'lbs',
        'category' => 'Updated Category',
    ]);

    // Assert
    $response->assertRedirect(route('shopping-lists.show', $this->shoppingList));
    $this->assertDatabaseHas('shopping_list_items', [
        'id' => $item->id,
        'name' => 'Updated Item',
        'quantity' => 3,
        'unit' => 'lbs',
        'category' => 'Updated Category',
    ]);
});

test('user can remove an item from their shopping list', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
    ]);

    // Act
    $response = $this->delete(route('shopping-lists.items.destroy', [$this->shoppingList, $item]));

    // Assert
    $response->assertRedirect(route('shopping-lists.show', $this->shoppingList));
    $this->assertDatabaseMissing('shopping_list_items', [
        'id' => $item->id,
    ]);
});

test('user can toggle purchase status of an item', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'is_purchased' => false,
    ]);

    // Act - Mark as purchased
    $response = $this->post(route('shopping-lists.items.toggle-purchased', [$this->shoppingList, $item]));

    // Assert
    $response->assertRedirect(route('shopping-lists.show', $this->shoppingList));
    $this->assertDatabaseHas('shopping_list_items', [
        'id' => $item->id,
        'is_purchased' => true,
    ]);

    // Act - Mark as not purchased
    $response = $this->post(route('shopping-lists.items.toggle-purchased', [$this->shoppingList, $item->fresh()]));

    // Assert
    $response->assertRedirect(route('shopping-lists.show', $this->shoppingList));
    $this->assertDatabaseHas('shopping_list_items', [
        'id' => $item->id,
        'is_purchased' => false,
    ]);
});

test('user cannot add items to another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $otherShoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // Act
    $response = $this->post(route('shopping-lists.items.store', $otherShoppingList), [
        'name' => 'Test Item',
    ]);

    // Assert
    $response->assertForbidden();
});

test('user cannot update items in another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $otherShoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $otherShoppingList->id,
    ]);

    // Act
    $response = $this->put(route('shopping-lists.items.update', [$otherShoppingList, $item]), [
        'name' => 'Updated Item',
    ]);

    // Assert
    $response->assertForbidden();
});

test('user cannot remove items from another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $otherShoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $otherShoppingList->id,
    ]);

    // Act
    $response = $this->delete(route('shopping-lists.items.destroy', [$otherShoppingList, $item]));

    // Assert
    $response->assertForbidden();
});

test('user cannot toggle purchase status of items in another users shopping list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $otherShoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $otherShoppingList->id,
        'is_purchased' => false,
    ]);

    // Act
    $response = $this->post(route('shopping-lists.items.toggle-purchased', [$otherShoppingList, $item]));

    // Assert
    $response->assertForbidden();
});
