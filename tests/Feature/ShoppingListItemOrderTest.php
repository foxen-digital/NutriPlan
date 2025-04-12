<?php

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);

    // Create some items with default order = 0
    $this->items = ShoppingListItem::factory()->count(3)->create([
        'shopping_list_id' => $this->shoppingList->id,
    ]);
});

test('can update item order', function () {
    // Arrange - reverse the order of item IDs for our test
    $itemIds = $this->items->pluck('id')->reverse()->values()->toArray();

    // Act
    $response = $this->put(route('shopping-lists.items.order', $this->shoppingList), [
        'item_ids' => $itemIds,
    ]);

    // Assert
    $response->assertRedirect(route('shopping-lists.show', ['shopping_list' => $this->shoppingList]));
    $response->assertSessionHas('success', 'Items reordered successfully.');

    // Check that the order was actually updated in the database
    foreach ($itemIds as $index => $id) {
        $this->assertDatabaseHas('shopping_list_items', [
            'id' => $id,
            'order' => $index + 1,
        ]);
    }
});

test('cannot update item order for another user\'s list', function () {
    // Arrange
    $otherUser = User::factory()->create();
    $otherShoppingList = ShoppingList::factory()->create([
        'user_id' => $otherUser->id,
    ]);
    $otherItems = ShoppingListItem::factory()->count(3)->create([
        'shopping_list_id' => $otherShoppingList->id,
    ]);

    // Act
    $response = $this->put(route('shopping-lists.items.order', $otherShoppingList), [
        'item_ids' => $otherItems->pluck('id')->toArray(),
    ]);

    // Assert
    $response->assertForbidden();
});

test('cannot update item order with invalid items', function () {
    // Arrange - include an item that doesn't belong to this shopping list
    $otherItem = ShoppingListItem::factory()->create();
    $invalidItemIds = array_merge($this->items->pluck('id')->toArray(), [$otherItem->id]);

    // Act
    $response = $this->put(route('shopping-lists.items.order', $this->shoppingList), [
        'item_ids' => $invalidItemIds,
    ]);

    // Assert
    $response->assertSessionHasErrors('item_ids.*');
});
