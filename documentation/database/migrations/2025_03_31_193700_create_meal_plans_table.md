# Documentation: 2025_03_31_193700_create_meal_plans_table.php

Original file: `database/migrations/2025_03_31_193700_create_meal_plans_table.php`

# 2025_03_31_193700_create_meal_plans_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The file `2025_03_31_193700_create_meal_plans_table.php` is a migration script designed for a Laravel application that manages meal planning for users. Its primary responsibility is to define the structure of the `meal_plans` database table, which stores the meal plans created by users. This migration ensures that the database schema is consistent and can be easily recreated or rolled back as necessary.

## Class Overview
The class within this file extends the `Migration` class provided by Laravel's database migrations system. This enables the class to define methods that facilitate the creation and deletion of database tables.

The `meal_plans` table is designed to hold information specific to meal planning, including details like user ownership, meal plan duration, and the number of people the plan accommodates.

## Methods

### up
```php
public function up(): void
```
#### Purpose
The `up` method is responsible for creating the `meal_plans` table, defining its structure and columns.

#### Parameters
- **None**

#### Return Values
- **void**: This method does not return any value.

#### Functionality
1. The method defines the creation of the `meal_plans` table through the use of Laravel's Schema facade.
2. It sets up the following columns:
   - `id`: An auto-incrementing primary key for uniquely identifying each meal plan.
   - `user_id`: A foreign key that references the `id` of the users table, establishing a relationship between meal plans and their respective users. It also ensures that if the user is deleted, all their meal plans will be deleted (`cascadeOnDelete()`).
   - `name`: A nullable string that allows users to assign a custom name to their meal plan.
   - `start_date`: A date field that indicates when the meal plan starts.
   - `duration`: An integer that holds the duration of the meal plan in days, with a comment specifying it should either be 7 or 14 days.
   - `people_count`: An integer to specify how many people the meal plan is intended for, with an explanatory comment.
3. The method also includes call to `timestamps()`, which automatically adds `created_at` and `updated_at` timestamp columns to track when the records are created and last updated.

### down
```php
public function down(): void
```
#### Purpose
The `down` method is used to reverse the operations defined in the `up` method, dropping the `meal_plans` table from the database.

#### Parameters
- **None**

#### Return Values
- **void**: This method does not return any value.

#### Functionality
1. It employs the `Schema::dropIfExists()` method to safely remove the `meal_plans` table if it exists. This is essential for maintaining database integrity and flexibility during the development lifecycle, as it allows for easy rolling back changes made in the `up` method.

## Conclusion
This migration script plays a crucial role in defining the `meal_plans` table within the database of a Laravel application, facilitating effective meal planning management for users. Understanding this file is essential for any developer working on the NutriPlan project, as it directly affects how meal plans are structured and stored in the database.