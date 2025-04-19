# Documentation: IngredientController.php

Original file: `app/Http/Controllers/IngredientController.php`

# IngredientController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Store Method](#store)
- [Routes](#routes)
- [Ingredient Model Relationships and Attributes](#ingredient-model)

## Introduction

The `IngredientController` class is a part of the NutriPlan application, specifically designed to manage ingredients within the system. This controller handles HTTP requests related to ingredient creation, allowing users to add new ingredients via the API. By utilizing Laravel's validation and response handling features, this controller ensures that only valid data is stored in the application. The main function provided in this controller is to create new ingredient records while enforcing uniqueness constraints.

## Store Method

### Purpose
The `store` method is responsible for accepting an HTTP request containing ingredient data, validating that data, and then creating a new ingredient entry in the database.

### Parameters
- `Request $request`: This parameter captures the incoming HTTP request that may contain data for a new ingredient.

### Return Value
- Returns a `JsonResponse` which includes the ID and name of the newly created ingredient.

### Functionality
The `store` method performs the following steps:
1. **Validation**: It validates the incoming request data. The data must meet the following criteria:
   - `name` is required.
   - `name` must be a string.
   - `name` must not exceed 255 characters.
   - `name` must be unique within the `ingredients` table.

   ```php
   $validated = $request->validate([
       'name' => ['required', 'string', 'max:255', Rule::unique('ingredients', 'name')],
   ]);
   ```

2. **Creation**: If the validation passes, a new ingredient is created in the database. The `is_common` attribute is set to `false` by default for newly created ingredients.

   ```php
   $ingredient = Ingredient::query()->create([
       'name' => $validated['name'],
       'is_common' => false,
   ]);
   ```

3. **Response**: Finally, the method returns a JSON response containing the `id` and `name` of the new ingredient.

   ```php
   return response()->json([
       'id' => $ingredient->id,
       'name' => $ingredient->name,
   ]);
   ```

## Routes

This controller primarily handles `POST` requests for storing ingredients. The typical route definition in a Laravel routing file would look like this:

```php
Route::post('/ingredients', [IngredientController::class, 'store']);
```

This route will direct any incoming `POST` requests to the `/ingredients` URI to the `store` method in the `IngredientController`.

## Ingredient Model Relationships and Attributes

### Relationships
The `Ingredient` model is typically related to other models in a database based on the requirements of the NutriPlan application. While this documentation does not provide explicit relationships, it is common for an ingredient to have associations such as:
- **Recipes**: Ingredients can be linked to multiple recipes.
- **Meal Plans**: Ingredients may also appear in meal plans, depending on how the application is structured.

### Important Attributes
- **name**: The name of the ingredient. This is a required attribute and must be unique.
- **is_common**: A boolean indicating whether the ingredient is common or uncommon. This is set to `false` by default when a new ingredient is created.

Understanding these aspects of the `IngredientController` and its associated model aids developers in grasping how to manage ingredients within the NutriPlan application efficiently.