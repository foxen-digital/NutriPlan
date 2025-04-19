# Documentation: MealPlan.php

Original file: `app/Models/MealPlan.php`

# MealPlan Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Properties](#class-properties)
- [Methods](#methods)
  - [user](#user)
  - [recipes](#recipes)
  - [days](#days)
  - [getEndDateAttribute](#getenddateattribute)
  
## Introduction
The `MealPlan` class represents a meal planning functionality within the NutriPlan application. It is part of the application's models and is responsible for managing the information related to a user's meal plan, including associated recipes and scheduled days. This class leverages Laravel's Eloquent ORM for database interactions, allowing for an expressive and convenient method of managing database records.

## Class Properties

### `$casts`
The `$casts` property is used to define how specific attributes should be transformed when the model is accessed. This affects both how data is stored in the database and how it is retrieved:

| Attribute     | Type     |
|---------------|----------|
| `start_date`  | `date`   |
| `duration`    | `integer`|
| `people_count`| `integer`|

### `$fillable` (Optional)
While not explicitly defined in the provided code, if implemented, this property would specify which attributes can be mass assigned when creating or updating the `MealPlan` instances.

## Methods

### user
```php
public function user(): BelongsTo
```
- **Purpose**: Retrieves the user associated with the meal plan.
- **Parameters**: None
- **Return Value**: Returns an instance of `BelongsTo`, indicating a one-to-many relationship where each meal plan belongs to one user.
  
#### Functionality
This method enables the retrieval of the user who created the meal plan, facilitating easy access to the user's details. This relationship is defined using Laravel's Eloquent ORM conventions.

### recipes
```php
public function recipes(): BelongsToMany
```
- **Purpose**: Retrieves the recipes associated with the meal plan.
- **Parameters**: None
- **Return Value**: Returns an instance of `BelongsToMany`, representing multiple recipes linked to a meal plan with additional pivot attributes.

#### Functionality
The `recipes` method defines a many-to-many relationship between `MealPlan` and `Recipe`. It uses a pivot table (`MealPlanRecipe`) to manage the associations, allowing for additional data such as `scale_factor` and `servings_available`. The `withTimestamps()` method indicates that created_at and updated_at timestamps should also be maintained on the pivot table.

### days
```php
public function days(): HasMany
```
- **Purpose**: Retrieves the scheduled days for the meal plan.
- **Parameters**: None
- **Return Value**: Returns an instance of `HasMany`, indicating a one-to-many relationship with the `MealPlanDay` model.

#### Functionality
This method allows you to fetch all the days associated with the meal plan, which are ordered by `day_number`. This is critical for meal planning as it provides the days in a chronological order that corresponds to the meal plan's duration.

### getEndDateAttribute
```php
public function getEndDateAttribute(): Carbon
```
- **Purpose**: Computes and retrieves the end date of the meal plan.
- **Parameters**: None
- **Return Value**: Returns an instance of `Carbon`, a date/time library, representing the end date of the meal plan.

#### Functionality
This accessor method calculates the end date by taking the `start_date` and adding the duration (minus one day, as the end date is inclusive of the start date). This is useful for displaying the duration of the meal plan to the user and for any associated logic regarding meal plan expiration.

## Summary
The `MealPlan` model provides essential functionalities to manage meal planning within the NutriPlan application. Through its methods, it facilitates relationships with users and recipes, while also managing the days associated with each meal plan. Overall, this class encapsulates the core data interactions needed for meal planning functionality. 

---

This documentation serves as a dedicated guide for developers looking to understand the `MealPlan` model in the NutriPlan system, detailing the purpose and functionality of each component within the class.