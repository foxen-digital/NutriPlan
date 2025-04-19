# Documentation: Recipe.php

Original file: `app/Models/Recipe.php`

# Recipe Documentation

## Table of Contents
- [Introduction](#introduction)
- [Attributes](#attributes)
- [Relationships](#relationships)
- [Methods](#methods)
    - [getSlugOptions](#getslugoptions)
    - [getRouteKeyName](#getroutekeyname)
    - [isImported](#isimported)
    - [user](#user)
    - [categories](#categories)
    - [ingredients](#ingredients)
    - [collections](#collections)
    - [nutritionInformation](#nutritioninformation)
    - [favoritedBy](#favoritedby)
    - [getMeasurementForIngredient](#getmeasurementforingredient)

## Introduction
The `Recipe` class represents a recipe in the NutriPlan application. It serves as a model that interacts with the underlying database to retrieve, manipulate, and store recipe-related data. This model utilizes various Laravel features, including Eloquent ORM functionalities and additional behavior provided by third-party packages. The primary responsibilities of the `Recipe` model include handling data related to recipes, managing relationships with users, categories, ingredients, and collections, and facilitating the retrieval and computation of relevant nutritional information.

## Attributes
The `Recipe` model has several properties defined by Eloquent:

| Attribute      | Type       | Description                                                    |
|----------------|------------|----------------------------------------------------------------|
| `cooking_time` | integer    | The time (in minutes) required to cook the recipe.           |
| `prep_time`    | integer    | The time (in minutes) required to prepare the recipe.        |
| `servings`     | integer    | The number of servings the recipe yields.                     |
| `images`       | array      | An array of image URLs associated with the recipe.            |
| `is_public`    | boolean    | Indicates whether the recipe is publicly accessible.          |
| `slug`         | string     | The SEO-friendly URL slug for the recipe (auto-generated).   |
| `user_id`      | integer    | The ID of the user who created the recipe (hidden).             |

## Relationships
The `Recipe` model defines several relationships that allow for the efficient retrieval of related data:

### `user()`
- Type: `BelongsTo`
- Description: Defines a relationship indicating that each recipe belongs to a single user.

### `categories()`
- Type: `BelongsToMany`
- Description: Retrieves categories associated with the recipe.

### `ingredients()`
- Type: `BelongsToMany`
- Description: Retrieves ingredients associated with the recipe. Includes a pivot model that captures additional information (amount, unit, description).

### `collections()`
- Type: `BelongsToMany`
- Description: Retrieves collections that contain the recipe.

### `nutritionInformation()`
- Type: `HasOne`
- Description: Retrieves nutritional information related to the recipe.

### `favoritedBy()`
- Type: `BelongsToMany`
- Description: Retrieves users who have favorited this recipe.

## Methods

### `getSlugOptions`
```php
public function getSlugOptions(): SlugOptions
```
- **Purpose**: Configures the slug generation for recipes.
- **Returns**: A `SlugOptions` instance that specifies how slugs are generated and stored.
- **Functionality**: This method uses the `Spatie\Sluggable` package to create a slug based on the recipe title and saves it to the `slug` attribute.

### `getRouteKeyName`
```php
public function getRouteKeyName(): string
```
- **Purpose**: Defines which attribute should be used for route model binding.
- **Returns**: A string indicating the attribute name (`slug`) to be used for locating the model in routes.
- **Functionality**: By overriding this method, the model can be matched in routes via the slug instead of the default primary key (ID).

### `isImported`
```php
public function isImported(): bool
```
- **Purpose**: Checks whether the recipe is imported from another source.
- **Returns**: A boolean value indicating if the recipe URL is set and non-empty.
- **Functionality**: This method determines if a recipe is imported by confirming the presence of a non-null and non-empty `url` attribute.

### `user`
```php
public function user(): BelongsTo
```
- **Purpose**: Defines the relationship between the recipe and its creator.
- **Returns**: An instance of `BelongsTo`, representing the user who owns the recipe.
- **Functionality**: This method allows for the retrieval of the user associated with the recipe.

### `categories`
```php
public function categories(): BelongsToMany
```
- **Purpose**: Retrieves the categories that the recipe is associated with.
- **Returns**: An instance of `BelongsToMany` representing the recipe's categories.
- **Functionality**: This method enables the retrieval of all categories linked to the recipe, allowing for categorization and filtering.

### `ingredients`
```php
public function ingredients(): BelongsToMany
```
- **Purpose**: Retrieves the ingredients utilized in the recipe.
- **Returns**: An instance of `BelongsToMany` allowing access to associated ingredients.
- **Functionality**: This method leverages a pivot table (`recipe_ingredient`) to manage the many-to-many relationship between recipes and ingredients. It tracks additional data such as amount, unit, and a description of the ingredient.

### `collections`
```php
public function collections(): BelongsToMany
```
- **Purpose**: Retrieves collections that include this recipe.
- **Returns**: An instance of `BelongsToMany`, representing the collections associated with the recipe.
- **Functionality**: This method facilitates the retrieval of all collections that a recipe belongs to, enabling better organization and access to related recipes.

### `nutritionInformation`
```php
public function nutritionInformation(): HasOne
```
- **Purpose**: Retrieves nutritional information for the recipe.
- **Returns**: An instance of `HasOne`, representing the nutrition information record.
- **Functionality**: This method allows direct access to the nutritional data related to the recipe, supporting dietary and nutritional calculations.

### `favoritedBy`
```php
public function favoritedBy(): BelongsToMany
```
- **Purpose**: Identifies users who have favorited this recipe.
- **Returns**: An instance of `BelongsToMany` representing users who have saved this recipe as a favorite.
- **Functionality**: This method allows the application to track user engagement with recipes, permitting functionality that displays popular or frequently favorited recipes.

### `getMeasurementForIngredient`
```php
public function getMeasurementForIngredient(Ingredient $ingredient): ?Measurement
```
- **Parameters**: 
    - `Ingredient $ingredient`: The ingredient for which the measurement is requested.
- **Returns**: A `Measurement` instance or `null` if no measurement is found.
- **Purpose**: Retrieves the measurement information related to a specific ingredient in the recipe.
- **Functionality**: This method looks up the associated ingredient within the recipe's ingredient collection through the pivot table, returning a `Measurement`