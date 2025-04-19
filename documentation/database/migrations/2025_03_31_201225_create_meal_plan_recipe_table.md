# Documentation: 2025_03_31_201225_create_meal_plan_recipe_table.php

Original file: `database/migrations/2025_03_31_201225_create_meal_plan_recipe_table.php`

# 2025_03_31_201225_create_meal_plan_recipe_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The file `2025_03_31_201225_create_meal_plan_recipe_table.php` is a migration script designed for use with the Laravel framework. This file is responsible for creating the `meal_plan_recipe` table in the database, which serves to link meal plans with their associated recipes. This relationship allows users to manage meal plans effectively by associating them with the recipes that make up each meal, facilitating a structured approach to meal planning within the NutriPlan application.

## Methods

### up
```php
public function up(): void
```

#### Purpose
The `up` method is responsible for executing the migration and creating the `meal_plan_recipe` table in the database schema. This method is called when the migration is run.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `up` method performs the following actions:
1. Calls `Schema::create()` to define a new table named `meal_plan_recipe`.
2. Inside the closure passed to the `create` method, it uses the `Blueprint` class to define the structure of the table:
   - `$table->id()`: This creates an auto-incrementing primary key column named `id`.
   - `$table->foreignId('meal_plan_id')->constrained()->cascadeOnDelete()`: This creates a foreign key column named `meal_plan_id`, which references the `id` column in the `meal_plans` table. The `cascadeOnDelete()` method ensures that if a meal plan is deleted, any associated records in this table are also removed.
   - `$table->foreignId('recipe_id')->constrained()->cascadeOnDelete()`: Similarly, this creates a foreign key column named `recipe_id`, which references the `id` column in the `recipes` table, also employing `cascadeOnDelete()`.
   - `$table->decimal('scale_factor', 8, 2)->default(1.0)->comment('Factor to scale recipe by')`: This adds a `scale_factor` column of type decimal, with a maximum of 8 digits and 2 decimal places. It has a default value of `1.0`, which allows for scaling of recipe ingredients when necessary (e.g., for different serving sizes).
   - `$table->timestamps()`: This adds `created_at` and `updated_at` timestamp columns to track when each record is created and last updated.

### down
```php
public function down(): void
```

#### Purpose
The `down` method defines the logic to reverse the migration. It is called when the migration is rolled back.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `down` method performs the following actions:
1. Calls `Schema::dropIfExists('meal_plan_recipe')`, which checks if the `meal_plan_recipe` table exists and drops it. This ensures that if the migration is rolled back, the table will be removed from the database schema, effectively undoing the changes made in the `up` method. 

## Technical Notes
- The migration utilizes Laravel's Eloquent ORM capabilities, allowing for easy management of database relationships and data integrity.
- Foreign key constraints are crucial for maintaining referential integrity between the `meal_plan_recipe`, `meal_plans`, and `recipes` tables.

This documentation aims to provide a clear understanding of the migration mechanism implemented in the `2025_03_31_201225_create_meal_plan_recipe_table.php` file, detailing its purpose, methods, and how it contributes to the overall functionality of the NutriPlan application.