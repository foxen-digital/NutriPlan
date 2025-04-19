# Documentation: MealPlanFactory.php

Original file: `database/factories/MealPlanFactory.php`

# MealPlanFactory Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Methods](#methods)
  - [definition](#definition)

## Introduction

The `MealPlanFactory.php` file defines a factory for creating `MealPlan` model instances using Laravel's built-in modeling and factory functionality. Factories are commonly used in Laravel applications for testing and database seeding purposes, allowing developers to quickly populate the database with sample data. The `MealPlanFactory` specifically generates meal plan data associated with users, facilitating realistic testing scenarios in codebases that manage meal planning functionalities.

## Class Overview

```php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MealPlan>
 */
class MealPlanFactory extends Factory
```

The `MealPlanFactory` class extends Laravel's `Factory` class, specifically for the `MealPlan` model. By using this class, developers can easily create new `MealPlan` instances with default attributes that simulate real-world usage.

### Relationships
- **User**: The `MealPlan` is associated with the `User` model, as each meal plan is created for a specific user via the `user_id` attribute.

## Methods

### definition

```php
public function definition(): array
```

#### Purpose
The `definition` method defines the default state of the `MealPlan` model. It outlines the attributes that will be generated when a factory creates a new instance of a `MealPlan`.

#### Parameters
- **None**

#### Return Values
- Returns an `array<string, mixed>` that represents the default attributes for a `MealPlan`.

#### Functionality
This method utilizes the Faker library to generate realistic fake data for testing. Each time this method is called, it returns a new array populated with:

| Attribute       | Type                | Description                                                        |
|------------------|---------------------|--------------------------------------------------------------------|
| `user_id`        | Integer (foreign key)| References the `User` ID, created through the `User::factory()` method. Leads to the relationship with the user that the meal plan belongs to. |
| `name`           | String              | A sentence, generated using Faker, representing the name of the meal plan. The sentence is optional, with a 70% chance of being produced. |
| `start_date`     | DateTime            | A random date between the current time and two weeks into the future, indicating when the meal plan starts.     |
| `duration`       | Integer             | Randomly selects between two values (7 or 14), indicating the duration of the meal plan in days.              |
| `people_count`   | Integer             | A random number between 1 and 8, representing how many people the meal plan is intended for.                 |

This structure allows for the quick generation of meal plans with varying attributes, which is crucial for testing and development processes.

### Example Usage
Here is an example of how a `MealPlan` can be created using the factory in a database seeder or test:

```php
use App\Models\MealPlan;

$mealPlan = MealPlan::factory()->create();
```

This code snippet will create a new `MealPlan` instance with random attributes defined in the `definition` method.