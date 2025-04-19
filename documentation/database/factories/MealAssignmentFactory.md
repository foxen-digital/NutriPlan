# Documentation: MealAssignmentFactory.php

Original file: `database/factories/MealAssignmentFactory.php`

# MealAssignmentFactory Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Methods](#methods)
  - [definition](#definition)
- [Usage](#usage)
- [Model Relationships](#model-relationships)

## Introduction

The `MealAssignmentFactory` class is part of the `Database\Factories` namespace and is designed for creating instances of the `MealAssignment` model within the context of a Laravel application. Factories in Laravel simplify the process of generating sample data for testing or seeding a database by encapsulating the logic for creating model instances with default attributes.

This factory is especially useful for testing purposes, where you need to generate `MealAssignment` entries along with their associated dependencies like `MealPlanDay` and `MealPlanRecipe`. It encapsulates the logic of setting up a consistent state for the `MealAssignment` model, which can be used in various tests throughout the application.

## Class Overview

```php
namespace Database\Factories;

use App\Models\MealAssignment;
use App\Models\MealPlanDay;
use App\Models\MealPlanRecipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MealAssignment>
 */
class MealAssignmentFactory extends Factory
{
    protected $model = MealAssignment::class;
    ...
}
```

The class extends the `Factory` class provided by Laravel, which helps to create instances of models. The `$model` property indicates that this factory is specifically for the `MealAssignment` model.

## Methods

### definition

```php
/**
 * Define the model's default state.
 *
 * @return array<string, mixed>
 */
public function definition(): array
{
    ...
}
```

#### Purpose

The `definition` method is a core component of the factory, responsible for defining the default state (attributes) for the `MealAssignment` model when it is instantiated.

#### Parameters

This method does not take any parameters.

#### Return Values

Returns an associative array where the keys are the names of the attributes of the `MealAssignment` model and the values are the default values. The array structure resembles:

```php
[
    'meal_plan_day_id' => int,
    'meal_plan_recipe_id' => int,
    'servings' => float,
    'to_cook' => bool,
]
```

#### Functionality

1. **Meal Plan Day Creation**: 
   - The method starts by creating a new `MealPlanDay` instance using its own factory.
   - This is necessary because each `MealAssignment` must be associated with a `MealPlanDay`.

2. **Meal Plan Recipe Association**:
   - A closure is used to define the `meal_plan_recipe_id`. The closure allows the factory to create a new `Recipe`, then associate it with the `MealPlan` from its linked `MealPlanDay`.
   - It retrieves the `MealPlan` associated with the newly created `MealPlanDay` and attaches the new `Recipe` to this `MealPlan` with a default `scale_factor`.

3. **Dynamic Servings Calculation**:
   - The relationship's pivot table is directly manipulated to update the `servings_available` based on the recipe’s servings and scale factor relative to the `MealPlan`'s people count.

4. **Random Servings and Cooking Flag**:
   - Finally, random values are generated for the `servings` (between 0.5 and 3.0) and `to_cook` (which is a boolean).

Each time this factory is invoked, it will create a new instance of `MealAssignment` with a unique state based on the logic defined in the `definition` method.

## Usage

To use this factory in your tests, you can call it as follows:

```php
$mealAssignment = MealAssignment::factory()->create();
```

This will generate a `MealAssignment` entry in the database with all the necessary related data, ensuring consistency in your test environment.

## Model Relationships

### MealAssignment Model

- **Relationships**:
  - **MealPlanDay**: Each `MealAssignment` belongs to a `MealPlanDay`.
  - **MealPlanRecipe**: Each `MealAssignment` is associated with a `MealPlanRecipe`.

### Attributes

- `meal_plan_day_id`: ID referencing the associated `MealPlanDay`.
- `meal_plan_recipe_id`: ID referencing the associated `MealPlanRecipe`.
- `servings`: Float indicating the number of servings for this meal assignment.
- `to_cook`: Boolean indicating whether this meal is designated to be cooked.

By documenting this factory class, developers can quickly understand how to generate related `MealAssignment` data, setting up an efficient testing process that reflects realistic scenarios in their application workflows. The core idea is to maintain a close relationship between the models while ensuring that every instance created has valid and necessary associated data.