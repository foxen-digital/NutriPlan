# Documentation: User.php

Original file: `app/Models/User.php`

# User Documentation

## Table of Contents

- [Introduction](#introduction)
- [Attributes](#attributes)
- [Methods](#methods)
    - [casts](#casts)
    - [getSlugOptions](#getslugoptions)
    - [getRouteKeyName](#getroutekeyname)
    - [recipes](#recipes)
    - [collections](#collections)
    - [favorites](#favorites)
    - [mealPlans](#mealplans)
    - [shoppingLists](#shoppinglists)

## Introduction

The `User` class is part of the NutriPlan application and serves as an Eloquent model representing the users of the application. It extends the `Authenticatable` class provided by Laravel, allowing it to leverage built-in authentication features. This model defines user-specific attributes, serialization behavior, and relationships with other models, contributing to the overall functionality of the NutriPlan system. By utilizing various traits such as `Notifiable`, `HasFactory`, `HasApiTokens`, and `HasSlug`, it seamlessly integrates notifications, factory-based user generation, API token management, and URL sluggification.

## Attributes

The `User` model has the following notable attributes:

| Attribute               | Visibility  | Description                                            |
|-------------------------|-------------|--------------------------------------------------------|
| `id`                    | Visible     | Unique identifier for the user.                       |
| `name`                  | Visible     | Name of the user.                                     |
| `slug`                  | Visible     | Slug generated from the user's name for URL usage.    |
| `password`              | Hidden      | Encrypted password for user authentication.            |
| `remember_token`        | Hidden      | Token used for "remember me" functionality during login. |
| `email_verified_at`     | Not directly visible | Timestamp indicating when the user's email was verified. |

## Methods

### casts

```php
protected function casts(): array
```

#### Purpose
Defines how certain attributes should be cast when the model is being serialized.

#### Return Values
- Returns an array mapping attribute names to their types.

#### Functionality
The `casts()` method defines specific data types for attributes, allowing for proper interpretation and storage of data. For instance:
- `email_verified_at` is cast to a `datetime` type.
- `password` is marked as `hashed`, indicating that it will be automatically encrypted when stored.

### getSlugOptions

```php
public function getSlugOptions(): SlugOptions
```

#### Purpose
Provides options for generating slugs that uniquely identify the user based on their name.

#### Return Values
- Returns a `SlugOptions` instance configuring the slug generation.

#### Functionality
This method uses the `create()` function from the `SlugOptions` class to set:
- The base field for slug generation as `name`.
- The database column to save the slug as `slug`. 

This ensures that the user's URL-friendly identifier is consistent and based on their name.

### getRouteKeyName

```php
public function getRouteKeyName(): string
```

#### Purpose
Specifies which attribute should be used when retrieving the model based on routes.

#### Return Values
- Returns the string `slug`.

#### Functionality
By overriding this method, the user can be retrieved using the `slug` attribute instead of the default `id`. This is important for RESTful route binding, ensuring that routes related to user resources are both readable and SEO-friendly.

### recipes

```php
public function recipes(): HasMany
```

#### Purpose
Defines a one-to-many relationship between users and recipes.

#### Return Values
- Returns a `HasMany` instance representing the relationship.

#### Functionality
This method allows the retrieval of all recipes associated with a given user. For example, `User::find(1)->recipes` retrieves all recipes created by the user with an ID of 1.

### collections

```php
public function collections(): HasMany
```

#### Purpose
Defines a one-to-many relationship between users and collections.

#### Return Values
- Returns a `HasMany` instance representing the relationship.

#### Functionality
Similar to the `recipes()` method, this returns all collections that a user has created. This enables features such as managing and displaying user-specific recipe collections.

### favorites

```php
public function favorites(): BelongsToMany
```

#### Purpose
Defines a many-to-many relationship between users and their favorite recipes.

#### Return Values
- Returns a `BelongsToMany` instance representing the relationship.

#### Functionality
This method facilitates the ability for users to manage their favorite recipes through a pivot table named `recipe_user_favorites`. The inclusion of `withTimestamps()` means that the relationship will also record timestamps for when the favorite was added or modified, providing additional context.

### mealPlans

```php
public function mealPlans(): HasMany
```

#### Purpose
Defines a one-to-many relationship between users and meal plans.

#### Return Values
- Returns a `HasMany` instance representing the relationship.

#### Functionality
This relationship allows users to create and manage multiple meal plans, essential for the meal planning feature of the application.

### shoppingLists

```php
public function shoppingLists(): HasMany
```

#### Purpose
Defines a one-to-many relationship between users and shopping lists.

#### Return Values
- Returns a `HasMany` instance representing the relationship.

#### Functionality
This method enables users to create multiple shopping lists, allowing for improved organization of ingredients and grocery management associated with their meal plans.

--- 

This documentation serves as a comprehensive guide to the `User` model in the NutriPlan application. It provides clarity on the class's purpose, its attributes, and its relationships, enabling developers to efficiently understand and utilize the User model within the system.