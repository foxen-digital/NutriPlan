# Documentation: Collection.php

Original file: `app/Models/Collection.php`

# Collection Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Methods](#methods)
  - [getSlugOptions](#getslugoptions)
  - [getRouteKeyName](#getroutekeyname)
  - [user](#user)
  - [recipes](#recipes)

## Introduction
The `Collection` class represents a model within the NutriPlan application, which is designed to manage and organize collections of recipes. This model leverages Laravel's Eloquent ORM to interact with the underlying database and includes functionality for slug generation, relationships to other models, and access control for certain attributes. The `Collection` model serves as a bridge between users and the recipes they curate, allowing for a structured approach to manage culinary data effectively.

## Class Definition
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Collection extends Model
{
    use HasFactory;
    use HasSlug;

    // Methods and properties defined below...
}
```

### Traits Used
- **HasFactory**: Enables the use of factory classes for testing and seeding.
- **HasSlug**: Provides functionality for managing slugs, allowing for SEO-friendly URLs.

## Methods

### getSlugOptions
```php
public function getSlugOptions(): SlugOptions
```
- **Purpose**: Configures the options for generating slugs using the Spatie Sluggable package.
- **Parameters**: None
- **Return Value**: Returns a `SlugOptions` instance that specifies how to generate and save slugs.
- **Functionality**:
  - This method defines the source field `name` for slug generation and specifies that the generated slug is to be saved in the `slug` field.
  - It leverages the fluent API of the `SlugOptions` class to ensure that all configurations are easily readable and maintainable.

### getRouteKeyName
```php
public function getRouteKeyName(): string
```
- **Purpose**: Customizes the route key for the model.
- **Parameters**: None
- **Return Value**: Returns a string that indicates the model's route key to use, which in this case is `slug`.
- **Functionality**:
  - By overriding this method, the model informs Laravel to use the `slug` instead of the default `id` when resolving routes that require a collection instance. This improves the readability of URLs by making them more descriptive.

### user
```php
public function user(): BelongsTo
```
- **Purpose**: Defines the relationship between the `Collection` and the `User` model.
- **Parameters**: None
- **Return Value**: Returns an instance of `BelongsTo`, indicating a many-to-one relationship with `User`.
- **Functionality**:
  - This method establishes a link between each collection and the user who owns it. In a typical use case, each collection will belong to a single user, allowing for access control and organization by user accounts.

### recipes
```php
public function recipes(): BelongsToMany
```
- **Purpose**: Defines the many-to-many relationship between `Collection` and `Recipe`.
- **Parameters**: None
- **Return Value**: Returns an instance of `BelongsToMany`, indicating a many-to-many relationship with `Recipe`.
- **Functionality**:
  - This method allows collections to hold multiple recipes, facilitating a customizable and flexible structure for recipe management. The underlying database would typically utilize a pivot table to manage this relationship, allowing for queries to fetch all recipes associated with a given collection.

## Attributes
- **hidden**: 
  - The `$hidden` array contains attributes that should not be included in the model's array or JSON form. In this case, `user_id` and `updated_at` are hidden, which are likely considered sensitive information or unnecessary for the client-facing representation.

## Conclusion
The `Collection` model is an integral part of the NutriPlan application, enabling users to manage their recipes in a user-friendly and organized manner. By implementing relationships with both the `User` and `Recipe` models and providing robust features such as slug generation, the `Collection` class effectively enhances the application's overall functionality. Understanding this model's methods and relationships is crucial for developers working with the NutriPlan codebase.