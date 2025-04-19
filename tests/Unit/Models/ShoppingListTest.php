<?php

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->shoppingList = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

test('shopping list has correct attribute casting', function () {
    // Arrange
    $list = new ShoppingList();

    // Assert
    expect($list->getCasts())
        ->toBe([
            'id' => 'int',
            'start_date' => 'date',
            'end_date' => 'date',
        ]);
});

test('shopping list belongs to a user', function () {
    // Act & Assert
    expect($this->shoppingList->user)
        ->toBeInstanceOf(User::class)
        ->and($this->shoppingList->user->id)->toBe($this->user->id);
});

test('shopping list has many items', function () {
    // Arrange
    $items = ShoppingListItem::factory()->count(3)->create([
        'shopping_list_id' => $this->shoppingList->id,
    ]);

    // Act & Assert
    expect($this->shoppingList->items)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(ShoppingListItem::class);
});

test('shopping list can be created with basic attributes', function () {
    // Arrange & Act
    $list = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Weekly Groceries',
    ]);

    // Assert
    expect($list)
        ->name->toBe('Weekly Groceries')
        ->user_id->toBe($this->user->id);
});

test('shopping list items are deleted when list is deleted', function () {
    // Arrange
    $items = ShoppingListItem::factory()->count(3)->create([
        'shopping_list_id' => $this->shoppingList->id,
    ]);

    // Act
    $this->shoppingList->delete();

    // Assert
    expect(ShoppingListItem::whereIn('id', $items->pluck('id'))->count())->toBe(0);
});

test('shopping list can track purchased items count', function () {
    // Arrange
    ShoppingListItem::factory()->count(2)->create([
        'shopping_list_id' => $this->shoppingList->id,
        'is_purchased' => true,
    ]);
    ShoppingListItem::factory()->count(3)->create([
        'shopping_list_id' => $this->shoppingList->id,
        'is_purchased' => false,
    ]);

    // Act & Assert
    expect($this->shoppingList->items()->where('is_purchased', true)->count())->toBe(2)
        ->and($this->shoppingList->items()->where('is_purchased', false)->count())->toBe(3);
});

test('shopping list can be created without optional dates', function () {
    // Arrange & Act
    $list = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Simple List',
    ]);

    // Assert
    expect($list)
        ->start_date->toBeNull()
        ->end_date->toBeNull();
});

test('shopping list dates are properly cast', function () {
    // Arrange
    $startDate = now();
    $endDate = now()->addDays(7);

    // Act
    $list = ShoppingList::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Dated List',
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]);

    // Assert
    expect($list)
        ->start_date->toBeInstanceOf(Carbon\Carbon::class)
        ->end_date->toBeInstanceOf(Carbon\Carbon::class)
        ->and($list->start_date->toDateString())->toBe($startDate->toDateString())
        ->and($list->end_date->toDateString())->toBe($endDate->toDateString());
});
