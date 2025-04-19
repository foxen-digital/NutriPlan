# Documentation: CollectionRecipeController.php

Original file: `app/Http/Controllers/CollectionRecipeController.php`

# CollectionRecipeController Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Store Method](#store)
   - [Parameters](#store-parameters)
   - [Return Value](#store-return-value)
   - [Functionality](#store-functionality)
3. [Destroy Method](#destroy)
   - [Parameters](#destroy-parameters)
   - [Return Value](#destroy-return-value)
   - [Functionality](#destroy-functionality)
4. [Route Handling](#route-handling)
5. [Related Models](#related-models)

## Introduction
The `CollectionRecipeController` handles the logic for associating recipes with collections within the NutriPlan application. It provides the ability for users to store and remove recipes from custom collections, effectively enabling users to organize their favorite recipes. This controller leverages specific actions and requests to ensure secure and efficient resource management.

## Store Method
```php
public function store(StoreCollectionRecipeRequest $request, AddRecipeToCollectionAction $action): RedirectResponse
```

### Purpose
The `store` method is responsible for storing a recipe in a specified collection. It performs validation and manages the business logic to associate the provided recipe with the specified collection.

### Parameters
- **StoreCollectionRecipeRequest** `$request`: 
  - A validated HTTP request containing:
    - `collection_id`: The ID of the collection to which the recipe will be added.
    - `recipe_id`: The ID of the recipe being added to the collection.
  
- **AddRecipeToCollectionAction** `$action`: 
  - An action object that encapsulates the logic needed to add a recipe to a collection.

### Return Value
- **RedirectResponse**: 
  - This method returns a redirect back to the previous location, with a success message indicating that the recipe has been successfully added to the collection.

### Functionality
1. **Validation**: The method first calls the `validated()` method on the `$request` object to ensure that the necessary data (collection ID and recipe ID) is present and valid.
2. **Fetching Models**: 
   - It retrieves the `Collection` and `Recipe` models using the validated IDs. If either model cannot be found, a `ModelNotFoundException` will be thrown.
3. **Handling Business Logic**: 
   - The `handle` method of the `$action` instance is invoked with the retrieved `Collection` and `Recipe` objects to perform the actual association logic.
4. **Response**: Finally, the method returns a redirect response with a flash message indicating success.

## Destroy Method
```php
public function destroy(Collection $collection, Recipe $recipe): RedirectResponse
```

### Purpose
The `destroy` method removes a recipe from a specified collection, ensuring that the relationship between the two entities is properly managed.

### Parameters
- **Collection** `$collection`: 
  - The `Collection` model instance from which the recipe will be removed.

- **Recipe** `$recipe`: 
  - The `Recipe` model instance that is to be detached from the collection.

### Return Value
- **RedirectResponse**: 
  - Similar to the `store` method, this returns back to the previous location with a success message confirming that the recipe has been removed from the collection.

### Functionality
1. **Authorization**: 
   - The method begins by authorizing the request using Laravel's Gate facade to ensure the user has permission to update the provided collection.
2. **Detaching Recipe**: 
   - The method calls the `detach()` function on the `recipes()` relationship of the `Collection` instance, passing the ID of the `Recipe` to remove the association.
3. **Response**: 
   - After successfully updating the relationship, it returns a redirect response with a confirmation message.

## Route Handling
Assuming Laravel's automatic resource routing, this controller will typically handle the following routes:

- `POST /collections/{collection}/recipes`: Maps to the `store` method for adding a recipe to a collection.
- `DELETE /collections/{collection}/recipes/{recipe}`: Maps to the `destroy` method for removing a recipe from a collection.

These routes enable CRUD operations related to recipes within collections, enhancing the application’s user functionality.

## Related Models

### Collection Model
- **Relationships**: 
  - A collection has many recipes, defined by the following method in the `Collection` model:
    ```php
    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }
    ```

### Recipe Model
- **Relationships**: 
  - A recipe can belong to many collections:
    ```php
    public function collections()
    {
        return $this->belongsToMany(Collection::class);
    }
    ```

### Important Attributes
- **Collection**:
  - `id`: Unique identifier of the collection.
  - `name`: Name of the collection.
  
- **Recipe**:
  - `id`: Unique identifier of the recipe.
  - `title`: Title of the recipe.
  - `instructions`: Cooking instructions associated with the recipe.

This document aims to provide thorough documentation of the `CollectionRecipeController`, elucidating its purpose, methods, and interaction with other models in the NutriPlan application.