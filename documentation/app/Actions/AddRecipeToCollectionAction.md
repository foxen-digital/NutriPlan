# Documentation: AddRecipeToCollectionAction.php

Original file: `app/Actions/AddRecipeToCollectionAction.php`

# AddRecipeToCollectionAction Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: `handle`](#method-handle)

## Introduction
The `AddRecipeToCollectionAction` class is part of the Actions namespace in the NutriPlan application. Its primary role is to manage the addition of recipes to a specific collection. This action encapsulates the logic required to ensure that a recipe can only be associated with a collection if it is not already present, thereby preventing duplicate entries. This functionality is crucial for maintaining data integrity within the recipe management system.

## Method: `handle`
The `handle` method is the core functionality of the `AddRecipeToCollectionAction` class. 

### Purpose
This method is responsible for adding a specified recipe to a given collection, ensuring that the recipe is not already associated with that collection.

### Parameters
| Parameter   | Type    | Description                                                     |
|-------------|---------|-----------------------------------------------------------------|
| `collection`| `Collection` | An instance of the `Collection` model representing the collection to which the recipe will be added. |
| `recipe`    | `Recipe` | An instance of the `Recipe` model representing the recipe that is to be added to the collection. |

### Return Value
- `void`: This method does not return any value. It performs its operation directly on the provided `Collection` instance.

### Functionality
The `handle` method performs the following steps:

1. **Check for Existing Association**: It first checks if the recipe already exists in the collection using the `exists` method. This check is important to ensure that duplicates are not created within the relationship.
   
   ```php
   if (!$collection->recipes()->where('recipe_id', $recipe->id)->exists()) {
   ```

2. **Attach Recipe**: If the recipe is not already associated with the collection, it attaches the recipe to the collection by calling the `attach` method on the `collection->recipes()` relationship.

   ```php
   $collection->recipes()->attach($recipe->id);
   ```

This design promotes the use of Eloquent relationships, making the process both simple and efficient, while adhering to the principles of good software design, such as ensuring the Single Responsibility Principle is respected.

### Example Usage
Below is an example of how you might use the `AddRecipeToCollectionAction` in a controller or another part of the system:

```php
use App\Models\Collection;
use App\Models\Recipe;
use App\Actions\AddRecipeToCollectionAction;

$collection = Collection::find($collectionId);
$recipe = Recipe::find($recipeId);

$action = new AddRecipeToCollectionAction();
$action->handle($collection, $recipe);
```

In this example, the action is initialized and executed, adding the specified recipe to the collection only if it isn't replicated. 

This documentation should help developers understand and utilize the `AddRecipeToCollectionAction` class effectively within the NutriPlan PHP application.