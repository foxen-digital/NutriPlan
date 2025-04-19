# Documentation: MealAssignment.php

Original file: `app/Models/MealAssignment.php`

# MealAssignment Documentation

## Table of Contents
- [Introduction](#introduction)
- [Attributes](#attributes)
- [Methods](#methods)
  - [mealPlanDay](#mealplanday)
  - [mealPlanRecipe](#mealplanrecipe)

## Introduction
The `MealAssignment.php` file defines the `MealAssignment` model within the NutriPlan application. This model represents the assignments of meals to specific meal plans in the context of meal planning and dietary tracking. It establishes relationships between the meal assignments, meal plan days, and recipes, enhancing the application's ability to manage and organize meals efficiently.

The `MealAssignment` model utilizes the Eloquent ORM features provided by Laravel, enabling easy interactions with a database representing meal assignments. It aims to facilitate functionality related to meal planning, such as managing servings and determining the meals to be cooked from assigned recipes.

## Attributes

The `MealAssignment` model has several important attributes:

| Attribute  | Type    | Description                                         |
|------------|---------|-----------------------------------------------------|
| `servings` | decimal | Represents the number of servings for the meal assignment, stored with two decimal places. |
| `to_cook`  | boolean | Indicates whether the meal is designated to be cooked (true) or not (false). |

The attributes are cast to their appropriate data types, ensuring correct handling of data within the application.

## Methods

### mealPlanDay
```php
public function mealPlanDay(): BelongsTo
```

#### Purpose
This method establishes a relationship between the `MealAssignment` model and the `MealPlanDay` model. It indicates that a meal assignment belongs to a specific meal plan day.

#### Parameters
- This method does not require any parameters.

#### Return Value
- Returns an instance of `BelongsTo`, which defines the relationship indicating that each meal assignment is associated with one meal plan day.

#### Functionality
The `mealPlanDay` method uses Eloquent's `belongsTo` function to create a relationship definition. This allows the retrieval of the meal plan day that the current meal assignment is linked to. By using this relationship, developers can easily access additional information about the meal plan day associated with a specific meal assignment.

### mealPlanRecipe
```php
public function mealPlanRecipe(): BelongsTo
```

#### Purpose
This method establishes a relationship between the `MealAssignment` model and the `MealPlanRecipe` model. It denotes that a meal assignment belongs to a particular meal plan recipe.

#### Parameters
- This method does not require any parameters.

#### Return Value
- Returns an instance of `BelongsTo`, which indicates that each meal assignment is associated with one meal plan recipe.

#### Functionality
Similar to the `mealPlanDay` method, the `mealPlanRecipe` method utilizes Eloquent's `belongsTo` function to create the relationship with the `MealPlanRecipe` model. This enables developers to retrieve the recipe associated with a specific meal assignment, thus facilitating meal preparation and planning tasks in the application.

By using both methods, developers can construct complex queries and retrieve related meal plan days and recipes efficiently, fostering a robust approach to managing meal assignments within the NutriPlan ecosystem.