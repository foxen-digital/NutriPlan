# Documentation: ShoppingListItemResource.php

Original file: `app/Http/Resources/ShoppingListItemResource.php`

# ShoppingListItemResource Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Method Documentation](#method-documentation)
  - [toArray](#toarray)

## Introduction
The `ShoppingListItemResource` class is part of the `App\Http\Resources` namespace within the NutriPlan application. This class serves as a resource for transforming the shopping list item data into a structured JSON format. It extends Laravel's `JsonResource`, allowing for easy and flexible manipulation of the data output. The primary role of this class is to present the attributes of a shopping list item in a way that can be directly consumed by APIs, facilitating a clean response structure for frontend applications.

## Class Overview
```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
```
- **Namespace**: `App\Http\Resources`
- **Parent Class**: `JsonResource` (from the Laravel framework)

### Purpose
By leveraging the `JsonResource`, this class ensures that shopping list items are returned in a consistent structure, making it easier for clients consuming the API to understand and interact with the data.

## Method Documentation

### toArray
```php
public function toArray(Request $request): array
```

#### Purpose
The `toArray` method transforms a `ShoppingListItem` model instance into an associative array. It provides a structured representation of the resource attributes that can be rendered as JSON.

#### Parameters
- **Request $request**: Current request instance that may contain additional information about the request context.

#### Return Value
- **array<string, mixed>**: An associative array containing the resource's attributes.

#### Functionality
The `toArray` method retrieves the attributes of the shopping list item and formats them into an array, including:

- `id`: The unique identifier for the shopping list item.
- `shopping_list_id`: The identifier for the shopping list that this item belongs to.
- `ingredient_id`: The identifier for the ingredient associated with this shopping list item.
- `name`: The name of the ingredient.
- `quantity`: The quantity specified for the ingredient.
- `unit`: The unit of measurement for the quantity.
- `category`: The category the ingredient belongs to.
- `is_custom`: A boolean indicating if the item is custom (as opposed to a standard ingredient).
- `is_purchased`: A boolean indicating if the item has been purchased.
- `created_at`: The timestamp when the item was created.
- `updated_at`: The timestamp when the item was last updated.
- `ingredient`: This includes the associated ingredient resource if it has been loaded; this is done using the `whenLoaded` method, which conditionally includes this relationship based on whether it was eager-loaded.

### Example
Here is an example of a response rendered by the `toArray` method:

```json
{
    "id": 1,
    "shopping_list_id": 10,
    "ingredient_id": 5,
    "name": "Tomatoes",
    "quantity": 3,
    "unit": "kg",
    "category": "Vegetables",
    "is_custom": false,
    "is_purchased": true,
    "created_at": "2023-10-01T12:00:00Z",
    "updated_at": "2023-10-02T12:00:00Z",
    "ingredient": {
        "id": 5,
        "name": "Tomato",
        "nutritional_info": {}
    }
}
```
The above JSON output demonstrates how the `ShoppingListItemResource` formats the shopping list item into a clear structure that can be easily consumed by clients.

---

This documentation serves as a comprehensive guide to understanding and utilizing the `ShoppingListItemResource` class within the NutriPlan application. It covers the class purpose, method functionalities, and an example response format, thereby equipping developers with the knowledge needed for effective integration and usage.