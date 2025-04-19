# Documentation: IngredientFactory.php

Original file: `database/factories/IngredientFactory.php`

# IngredientFactory Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Methods](#methods)
  - [definition](#definition)
  - [common](#common)
  - [forRecipe](#forrecipe)

## Introduction
The `IngredientFactory` is a part of the `Database\Factories` namespace within the NutriPlan application. It handles the creation of `Ingredient` model instances for testing and seeding the database. This factory utilizes the built-in Laravel Factory feature to efficiently generate and manage `Ingredient` data, which is crucial for populating the application with sample data. This factory is particularly useful for creating various test scenarios and for setting up the database with necessary records.

## Class Definition
```php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MeasurementUnit;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
   ...
}
```

## Methods

### definition
```php
public function definition(): array
```
#### Purpose
The `definition` method defines the default state of the `Ingredient` model. It specifies the basic attributes that each ingredient will have when created using this factory.

#### Parameters
- This method does not accept any parameters.

#### Return Values
- Returns an array consisting of the default attributes for the `Ingredient` model.

#### Functionality
- This method uses the `fake()` function to generate a unique word for the `name` attribute of the ingredient. 
- The `is_common` attribute is set to `false` by default, indicating that the generated ingredient is not one typically found in kitchens.

### common
```php
public function common(): static
```
#### Purpose
The `common` method allows for the creation of an ingredient that is marked as commonly found in kitchens.

#### Parameters
- This method does not accept any parameters.

#### Return Values
- Returns an instance of the factory with modified state to indicate that the ingredient is common.

#### Functionality
- Modifies the state of the ingredient to set `is_common` to `true`. This is done using the `state` method, which modifies the attributes before the ingredient is created.

### forRecipe
```php
public function forRecipe(): static
```
#### Purpose
The `forRecipe` method facilitates the creation of an ingredient along with its relationship to a recipe. This is useful for testing purposes where ingredients need to be associated with specific recipes.

#### Parameters
- This method does not accept any parameters.

#### Return Values
- Returns an instance of the factory with the specified state for relationship creation.

#### Functionality
- Uses the `state` method to allow creating an ingredient that's associated with a recipe.
- Within the `afterCreating` callback:
  - It randomly determines if the `amount` of the ingredient should be `null` (20% chance, representing ingredients used "to taste").
  - The recipe is then created using `Recipe::factory()->create()`, and the association is established using the `attach` method.
  - Alongside the recipe, the method attaches additional attributes: 
    - `amount`: A random float between 0.25 and 10 or `null`.
    - `unit`: A random measurement unit value fetched from the `MeasurementUnit` enum.
    - `description`: An optional sentence that serves as a description for the ingredient.

This setup allows for rich and varied test data that reflects real-world usage scenarios.

## Conclusion
The `IngredientFactory` is an essential component of the NutriPlan application, streamlining the process of generating test data for ingredients and their relationships with recipes. Its methods are structured to provide both random and specific data, allowing developers to effectively test the application under different scenarios. Understanding this factory enables easy adjustments and enhancements in test data generation, thereby improving the overall quality of the application.