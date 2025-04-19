# Documentation: MealPlanRecipe.php

Original file: `app/Models/MealPlanRecipe.php`

# MealPlanRecipe Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Attributes](#attributes)
- [Methods](#methods)
  - [calculateAvailableServings](#calculateavailableservings)
  - [mealPlan](#mealplan)
  - [recipe](#recipe)

## Introduction
The `MealPlanRecipe` class is a model that represents the pivot table `meal_plan_recipe` in the NutriPlan application. This class is utilized within the context of meal planning, specifically to associate recipes with their respective meal plans while also permitting the calculation of key metrics, such as available servings based on the number of people in the meal plan and the scale factor of the recipe.

As a pivot model, it facilitates many-to-many relationships between the `Recipe` and `MealPlan` models, enabling seamless interactions and data handling among meal plans and the various recipes associated with them.

## Class Overview
The `MealPlanRecipe` class extends the `Pivot` class provided by Eloquent, the ORM (Object-Relational Mapping) tool used by Laravel. It primarily manages the relationship between meal plans and recipes while also carrying additional logistical calculations related to servings.

### Attributes

| Attribute              | Type                      | Description                                                                        |
|------------------------|---------------------------|------------------------------------------------------------------------------------|
| `$table`               | `string`                  | Specifies the name of the database table associated with the model.                |
| `$incrementing`        | `bool`                    | Indicates if the IDs of the model are auto-incrementing; defaults to `true`.      |
| `$casts`               | `array<string, string>`   | Defines the type casting for specific attributes, enabling the automatic conversion of types. |

### `protected $casts`
The `$casts` property is used to specify how certain attributes should be converted when accessing or storing them. In this model:
- `scale_factor` and `servings_available` are cast to `decimal:2`, indicating they will store decimal values up to two decimal places.

## Methods

### `calculateAvailableServings`
```php
public function calculateAvailableServings(): void
```
#### Purpose
This method calculates and updates the number of servings available for the associated recipe based on the meal plan's scale factor and the count of people represented in the meal plan.

#### Parameters
- None

#### Return Values
- Void (the method does not return a value)

#### Functionality
1. The method begins by checking if the instance has a valid associated `recipe` and `mealPlan`. If either is missing, the method returns early without performing further calculations.
2. It retrieves the number of servings from the associated recipe.
3. The total available servings for the meal plan recipe are computed by multiplying the recipe's servings by the scale factor defined for this meal plan.
4. The method then checks the `people_count` attribute of the meal plan; if this count is less than or equal to zero, it sets `servings_available` to zero and exits.
5. If the number of people is valid, it calculates `servings_available` by dividing the total plan servings by the number of people.

### `mealPlan`
```php
public function mealPlan(): BelongsTo
```
#### Purpose
This method defines the relationship between the `MealPlanRecipe` class and the `MealPlan` model, establishing a `belongsTo` relationship.

#### Parameters
- None

#### Return Values
- `BelongsTo`: An instance of the `BelongsTo` relationship indicating which meal plan this recipe belongs to.

#### Functionality
This method enables access to the containing `MealPlan` model for the given pivot instance, making it easy to retrieve the meal plan details associated with the current recipe.

### `recipe`
```php
public function recipe(): BelongsTo
```
#### Purpose
This method describes the relationship between the `MealPlanRecipe` class and the `Recipe` model, establishing the `belongsTo` association.

#### Parameters
- None

#### Return Values
- `BelongsTo`: An instance of the `BelongsTo` relationship indicating which recipe this pivot is related to.

#### Functionality
Similar to the `mealPlan` method, this provides an interface to access the associated `Recipe` model for a given pivot instance. It allows developers to fetch detailed information about the recipe associated with a meal plan.

---

This documentation serves as a reference for developers working with the `MealPlanRecipe` class, providing insight into its structure, relationships, and functionalities. Understanding this pivot model is crucial for effectively managing meal plans and associated recipes in the NutriPlan application.