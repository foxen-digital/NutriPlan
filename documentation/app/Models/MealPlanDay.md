# Documentation: MealPlanDay.php

Original file: `app/Models/MealPlanDay.php`

# MealPlanDay Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Attributes](#attributes)
- [Methods](#methods)
  - [mealPlan](#mealplan)
  - [mealAssignments](#mealassignments)
  - [getDateAttribute](#getdateattribute)

## Introduction
The `MealPlanDay` class represents a single day within a meal plan in the NutriPlan web application. This model is responsible for managing the day's attributes, relationships with meal plans, and meal assignments. By leveraging Eloquent, the ORM provided by Laravel, this class facilitates easy interaction with the database while allowing for relationships that enhance the application's functionality.

## Class Definition
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```
The `MealPlanDay` class is defined using the `Model` base class provided by Laravel. It uses the `HasFactory` trait, allowing the model to utilize Laravel's factory features for database seeding and testing.

## Attributes
### Casts
The `casts` property defines how attributes should be transformed when they are accessed. In this case, the `day_number` attribute is cast to an integer.

| Attribute    | Type   |
|--------------|--------|
| day_number   | integer|

### Appends
The `appends` property specifies additional attributes to be included when the model is converted to an array or JSON. Here, the `date` attribute is appended.

| Attribute    | Type    |
|--------------|---------|
| date         | string  |

## Methods
### mealPlan
```php
public function mealPlan(): BelongsTo
```
#### Purpose
This method defines a relationship where each `MealPlanDay` belongs to a `MealPlan`.

#### Return Value
Returns an instance of `BelongsTo`, establishing a connection between `MealPlanDay` and its parent `MealPlan`.

#### Functionality
The method does not take any parameters. When called, it retrieves the associated `MealPlan` for the day, allowing for accessing meal plan details directly from the meal plan day instance.

### mealAssignments
```php
public function mealAssignments(): HasMany
```
#### Purpose
This method establishes a one-to-many relationship between the `MealPlanDay` and `MealAssignment`.

#### Return Value
Returns an instance of `HasMany`, facilitating access to all meal assignments related to that specific day.

#### Functionality
This method does not accept parameters. When invoked, it allows the retrieval of all `MealAssignment` records associated with the `MealPlanDay`, enabling developers to work with the meals scheduled for that day easily.

### getDateAttribute
```php
public function getDateAttribute(): string
```
#### Purpose
This accessor computes the `date` for the meal plan day based on its `day_number` and the start date of the associated meal plan.

#### Return Value
Returns a string representation of the date in the `YYYY-MM-DD` format.

#### Functionality
- The function checks if the `mealPlan` relation is loaded; if not, it loads the `mealPlan` to ensure that the necessary data is available.
- It then calculates the date by adding the `day_number - 1` days to the start date of the `mealPlan`.
- Finally, the computed date is converted to a string format before being returned.

## Conclusion
The `MealPlanDay` class is a critical component in the NutriPlan application, encapsulating the day of a meal plan and its interactions with meal assignments and the parent meal plan. Through its well-defined attributes and relationships, it aids in managing meal scheduling and enhances the overall user experience in meal planning.

By understanding and utilizing the `MealPlanDay` model, developers can effectively integrate meal planning functionalities into the NutriPlan application, ensuring better organization of meal schedules for users.