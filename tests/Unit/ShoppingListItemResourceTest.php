<?php

use App\Http\Resources\ShoppingListItemResource;
use App\Models\Ingredient;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;

beforeEach(function () {
    $this->shoppingList = ShoppingList::factory()->create();
    $this->ingredient = Ingredient::factory()->create();
});

test('resource transforms basic shopping list item correctly', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'name' => 'Test Item',
        'quantity' => 2.5,
        'unit' => 'cups',
        'category' => 'Dairy',
        'is_custom' => true,
        'is_purchased' => false,
    ]);

    // Act
    $resource = new ShoppingListItemResource($item);
    $array = $resource->toArray(request());

    // Assert
    expect($array)
        ->toBeArray()
        ->toHaveKeys([
            'id',
            'shopping_list_id',
            'ingredient_id',
            'name',
            'quantity',
            'unit',
            'category',
            'is_custom',
            'is_purchased',
            'created_at',
            'updated_at',
        ])
        ->and($array['name'])->toBe('Test Item')
        ->and((float)$array['quantity'])->toBe(2.5)
        ->and($array['unit'])->toBe('cups')
        ->and($array['category'])->toBe('Dairy')
        ->and($array['is_custom'])->toBeTrue()
        ->and($array['is_purchased'])->toBeFalse();
});

test('resource includes ingredient relationship when loaded', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $this->ingredient->id,
        'name' => 'Test Item',
    ]);

    $item->load('ingredient');

    // Act
    $resource = new ShoppingListItemResource($item);
    $array = $resource->toArray(request());

    // Assert
    expect($array)
        ->toHaveKey('ingredient')
        ->and($array['ingredient'])->not->toBeNull()
        ->and($array['ingredient']['id'])->toBe($this->ingredient->id);
});

test('resource excludes ingredient relationship when not loaded', function () {
    // Arrange
    $item = ShoppingListItem::factory()->create([
        'shopping_list_id' => $this->shoppingList->id,
        'ingredient_id' => $this->ingredient->id,
        'name' => 'Test Item',
    ]);

    // Act
    $resource = new ShoppingListItemResource($item);
    $array = $resource->toArray(request());

    // Assert
    expect($array)
        ->toHaveKey('ingredient')
        ->and($array['ingredient'])->toBeInstanceOf(\Illuminate\Http\Resources\MissingValue::class);
});
