# Documentation: RecipeIngredient.php

Original file: `app/Models/RecipeIngredient.php`

# RecipeIngredient Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Attributes](#attributes)
- [Relationships](#relationships)
- [Methods](#methods)
  - [recipe](#recipe)
  - [ingredient](#ingredient)
  - [measurement](#measurement)

## Introduction

The `RecipeIngredient.php` file defines the `RecipeIngredient` class, which serves as a pivot model in the Laravel Eloquent ORM to manage the relationship between `Recipe` and `Ingredient` models. It represents the many-to-many relationship between recipes and their corresponding ingredients, including relevant attributes such as the amount and unit of measurement for each ingredient used in a recipe. This class also encapsulates the logic required to handle measurement conversions through the `Measurement` value object.

## Class Definition

```php
class RecipeIngredient extends Pivot
```

The `RecipeIngredient` class extends `Pivot`, indicating that it is used to bridge two Eloquent models, specifically `Recipe` and `Ingredient`, allowing for the storage of additional data about the relationship.

## Attributes

The `RecipeIngredient` class contains the following attributes:

| Attribute | Type   | Description                                      |
|-----------|--------|--------------------------------------------------|
| `amount`  | float  | The quantity of the ingredient required for the recipe |
| `unit`    | string | The measurement unit for the ingredient (e.g., grams, pieces) |

### Casts

- The `amount` attribute is automatically cast to a float for precise numerical operations.

## Relationships

The `RecipeIngredient` class defines two important relationships:

- **Recipe**: Represents the relationship to the `Recipe` model.
- **Ingredient**: Represents the relationship to the `Ingredient` model.

These relationships allow for easy access to the associated recipe and ingredient data.

## Methods

### recipe

```php
public function recipe(): BelongsTo
```

**Purpose**: 
This method defines the inverse relationship to the `Recipe` model.

**Returns**: 
An instance of `BelongsTo`, representing the relationship from this pivot model to the `Recipe` model.

**Functionality**:
- Calls the Eloquent `belongsTo` method to establish the relationship, making it possible to retrieve the recipe associated with this ingredient.

### ingredient

```php
public function ingredient(): BelongsTo
```

**Purpose**:
This method defines the inverse relationship to the `Ingredient` model.

**Returns**: 
An instance of `BelongsTo`, representing the relationship from this pivot model to the `Ingredient` model.

**Functionality**:
- Similar to the `recipe` method, this method utilizes the `belongsTo` method to define the relationship with the `Ingredient` model, thereby allowing easy access to the ingredient associated with this recipe ingredient.

### measurement

```php
public function measurement(): Measurement
```

**Purpose**: 
This method converts the ingredient's amount and unit into a `Measurement` value object.

**Returns**: 
An instance of `Measurement`, which encapsulates the amount and unit of the ingredient.

**Functionality**:
- Retrieves the `unit` of the ingredient.
- If the `unit` is a string, it attempts to convert it into a `MeasurementUnit` enum using the `tryFrom` method, falling back to the `MeasurementUnit::PIECE` if the conversion is unsuccessful.
- Constructs and returns a new `Measurement` object, using the `amount` and resolved `unit`. This encapsulation promotes better type safety and clearer handling of measurement-related logic.

---

This documentation serves as a complete guide to understanding the `RecipeIngredient` class within the NutriPlan application, outlining its structure, relationships, and functionalities, thus enabling developers to utilize and extend it effectively in their work.