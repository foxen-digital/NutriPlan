# Documentation: ShoppingListResource.php

Original file: `app/Http/Resources/ShoppingListResource.php`

# ShoppingListResource Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Method Documentation](#method-documentation)
  - [toArray](#toarray)

## Introduction

The `ShoppingListResource` class is part of the `App\Http\Resources` namespace in the NutriPlan application. It extends `JsonResource` from the Laravel framework and is used to transform a `ShoppingList` model into a JSON representation suitable for API responses. This resource ensures that only relevant attributes are included in responses and provides additional functionality to load related data such as items in the shopping list.

## Class Definition

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingListResource extends JsonResource
{
    // Class methods and properties...
}
```

### Purpose
This class serves as a bridge between the `ShoppingList` model and the JSON format returned to the client. By encapsulating the transformation logic, it promotes the separation of concerns, making the codebase cleaner and more maintainable.

## Method Documentation

### `toArray`

```php
public function toArray(Request $request): array
```

#### Purpose
The `toArray` method transforms the resource into an array format. This array can be easily converted into JSON as a response from an API endpoint.

#### Parameters
- **Request $request**: An instance of the `Request` object that contains the current request data. This parameter allows access to request-specific information that may influence how the resource is rendered.

#### Return Value
- **array<string, mixed>**: Returns an associative array containing the serialized representation of the `ShoppingList` model. The structure of this array includes basic attributes, relationships, and counts of related items.

#### Functionality
- The method returns an array that includes:
  - `id`: The unique identifier for the shopping list.
  - `name`: The name of the shopping list.
  - `user_id`: The identifier of the user that owns the shopping list.
  - `created_at`: The timestamp of when the list was created.
  - `updated_at`: The timestamp of when the list was last updated.
  - `items`: A collection of related `ShoppingListItemResource` items loaded lazily when the `items` relationship is available.
  - `items_count`: The total number of items associated with the shopping list, calculated dynamically using the `whenCounted` method.

##### Example Output 
The output of the `toArray` method might look like this when the shopping list with items is transformed:

```json
{
  "id": 1,
  "name": "Weekly Groceries",
  "user_id": 5,
  "created_at": "2023-01-01T12:00:00Z",
  "updated_at": "2023-01-02T12:00:00Z",
  "items": [
    {
      "id": 1,
      "name": "Apple",
      "quantity": 5
    },
    {
      "id": 2,
      "name": "Banana",
      "quantity": 2
    }
  ],
  "items_count": 2
}
```

### Additional Considerations
- This method leverages Laravel's built-in resource features to ensure that relationships are loaded efficiently and conditionally include data only when necessary.
- It provides a clean API response format that aligns with the principles of RESTful API design, enhancing usability for clients consuming the API.

By utilizing the `ShoppingListResource`, developers can maintain consistency and manage the data output for the shopping list entity, keeping the API responses clean and relevant to client needs.