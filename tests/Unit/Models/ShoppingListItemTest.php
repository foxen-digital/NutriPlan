<?php

use App\Models\Ingredient;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;

beforeEach(function () {
    $this->shoppingList = ShoppingList::factory()->create();
    $this->ingredient = Ingredient::factory()->create();
});

test('shopping list item has correct fillable attributes', function () {
    // Arrange
    $item = new ShoppingListItem();

    // Assert
    expect($item->getFillable())->toBe([
        'ingredient_id',
        'name',
        'quantity',
        'unit',
        'category',
        'is_custom',
        'is_purchased',
        'order',
    ]);
});

test('shopping list item has correct attribute casting', function () {
    // Arrange
    $item = new ShoppingListItem();

    // Assert
    expect($item->getCasts())
        ->toBe([
            'id' => 'int',
            'quantity' => 'decimal:2',
            'is_custom' => 'boolean',
            'is_purchased' => 'boolean',
            'order' => 'integer',
        ]);
});

test('shopping list item belongs to a shopping list', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
    ]);

    // Act & Assert
    expect($item->shoppingList)
        ->toBeInstanceOf(ShoppingList::class)
        ->and($item->shoppingList->id)->toBe($this->shoppingList->id);
});

test('shopping list item can belong to an ingredient', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $this->ingredient->id,
    ]);

    // Act & Assert
    expect($item->ingredient)
        ->toBeInstanceOf(Ingredient::class)
        ->and($item->ingredient->id)->toBe($this->ingredient->id);
});

test('shopping list item can be created with basic attributes', function () {
    // Arrange & Act
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'name' => 'Test Item',
        'quantity' => 2.5,
        'unit' => 'cups',
        'category' => 'Dairy',
        'is_custom' => true,
        'is_purchased' => false,
        'order' => 1,
    ]);

    // Assert
    expect($item)
        ->name->toBe('Test Item')
        ->quantity->toBe('2.50')
        ->unit->toBe('cups')
        ->category->toBe('Dairy')
        ->is_custom->toBeTrue()
        ->is_purchased->toBeFalse()
        ->order->toBe(1);
});

test('shopping list item can be marked as purchased', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'is_purchased' => false,
    ]);

    // Act
    $item->update(['is_purchased' => true]);

    // Assert
    expect($item->is_purchased)->toBeTrue();
});

test('shopping list item can be reordered', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'order' => 1,
    ]);

    // Act
    $item->update(['order' => 5]);

    // Assert
    expect($item->order)->toBe(5);
});

test('shopping list item can be created without optional attributes', function () {
    // Arrange & Act
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'name' => 'Test Item',
        'quantity' => null,
        'unit' => null,
        'category' => null,
    ]);

    // Assert
    expect($item)
        ->name->toBe('Test Item')
        ->quantity->toBeNull()
        ->unit->toBeNull()
        ->category->toBeNull();
});
