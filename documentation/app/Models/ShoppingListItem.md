# Documentation: ShoppingListItem.php

Original file: `app/Models/ShoppingListItem.php`

# ShoppingListItem Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Properties](#class-properties)
  - [fillable](#fillable)
  - [casts](#casts)
- [Methods](#methods)
  - [shoppingList](#shoppinglist)
  - [ingredient](#ingredient)
- [Relationships](#relationships)

## Introduction
The `ShoppingListItem` class serves as a model representing items that can be included in a shopping list within the NutriPlan application. This class is part of the Laravel framework's model layer, leveraging Eloquent ORM to interact with the database. The `ShoppingListItem` holds data attributes related to specific items, including quantity, unit of measure, whether the item is custom, its purchased status, and its order in the list. This model is particularly important for managing user shopping lists and tracking which ingredients they need.

## Class Properties

### fillable
The `$fillable` property is an array that specifies which attributes should be mass-assignable. The purpose of this feature is to protect against mass assignment vulnerabilities by explicitly defining which attributes are safe to assign when creating or updating a `ShoppingListItem`. 

```php
protected $fillable = [
    'name',
    'quantity',
    'unit',
    'category',
    'is_custom',
    'is_purchased',
    'order',
];
```

| Attribute    | Type      | Description                                          |
|--------------|-----------|------------------------------------------------------|
| name         | string    | The name of the shopping list item                    |
| quantity     | decimal   | The quantity of the item to purchase                 |
| unit         | string    | The unit of measurement (e.g., kg, liters)          |
| category     | string    | Category to which the item belongs                    |
| is_custom    | boolean   | Indicates if the item is custom-made                 |
| is_purchased | boolean   | Status of whether the item has been purchased        |
| order        | integer   | The order in which the item appears in the list      |

### casts
The `$casts` property defines how certain attributes should be cast when the model is retrieved from or saved to the database. This feature allows for type enforcement, ensuring that the data is in the expected format for processing.

```php
protected $casts = [
    'quantity' => 'decimal:2',
    'is_custom' => 'boolean',
    'is_purchased' => 'boolean',
    'order' => 'integer',
];
```

| Attribute     | Cast Type | Description                                         |
|---------------|-----------|-----------------------------------------------------|
| quantity      | decimal   | Casts the quantity to a decimal with 2 decimal points |
| is_custom     | boolean   | Casts the is_custom attribute to a boolean value    |
| is_purchased  | boolean   | Casts the is_purchased attribute to a boolean value |
| order         | integer   | Casts the order attribute to an integer value       |

## Methods

### shoppingList
This method defines a relationship indicating that each `ShoppingListItem` belongs to a `ShoppingList`. This is useful for retrieving the shopping list associated with the item.

```php
public function shoppingList(): BelongsTo
{
    return $this->belongsTo(ShoppingList::class);
}
```

**Return Value:**  
Returns an instance of `BelongsTo`, which sets up the relationship to the `ShoppingList` model.

**Functionality:**  
- This method enables developers to access the associated shopping list of a particular shopping list item easily. 
- For example, `$shoppingListItem->shoppingList()` will return the `ShoppingList` object associated with that item, allowing for further operations or queries related to the shopping list.

### ingredient
Similar to the `shoppingList` method, this method establishes a relationship indicating that each `ShoppingListItem` can be linked to an `Ingredient`. This is crucial for identifying which ingredient is tied to the shopping list item.

```php
public function ingredient(): BelongsTo
{
    return $this->belongsTo(Ingredient::class);
}
```

**Return Value:**  
Returns an instance of `BelongsTo`, which sets up the relationship to the `Ingredient` model.

**Functionality:**  
- This method allows for easy access to the `Ingredient` associated with the shopping list item. 
- For instance, calling `$shoppingListItem->ingredient()` will provide the `Ingredient` object that corresponds to the item, facilitating integration of ingredient details into the shopping list.

## Relationships
The `ShoppingListItem` class has two key relationships:

- **BelongsTo ShoppingList**: Each item is related to a specific shopping list.
- **BelongsTo Ingredient**: Each item corresponds to an ingredient, linking shopping items to the ingredients needed for recipes.

These relationships simplify the management of shopping list items, making it easier to query and manipulate data related to shopping lists and ingredients within the NutriPlan system.