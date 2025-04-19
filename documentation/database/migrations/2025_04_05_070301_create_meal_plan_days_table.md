# Documentation: 2025_04_05_070301_create_meal_plan_days_table.php

Original file: `database/migrations/2025_04_05_070301_create_meal_plan_days_table.php`

# 2025_04_05_070301_create_meal_plan_days_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
  - [up() Method](#up-method)
  - [down() Method](#down-method)

## Introduction
The `2025_04_05_070301_create_meal_plan_days_table.php` file is a migration file in a Laravel-based PHP application, specifically designed to manage the database schema for meal plan days. This file plays a crucial role in setting up the corresponding `meal_plan_days` table in the database. It defines the fields and constraints for the table, which stores information related to different days in meal plans, ensuring a structured and organized approach to meal plan management.

## Class Overview
The class in this migration extends the `Migration` class provided by Laravel and contains two key methods: `up()` and `down()`. These methods define how to create and delete the `meal_plan_days` table, respectively.

### up() Method
```php
public function up(): void
{
    Schema::create('meal_plan_days', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('meal_plan_id')->constrained()->onDelete('cascade');
        $table->unsignedInteger('day_number');
        $table->timestamps();

        $table->unique(['meal_plan_id', 'day_number']);
    });
}
```

#### Purpose
The `up()` method is responsible for creating the `meal_plan_days` table in the database.

#### Parameters
- None.

#### Return Values
- None (void).

#### Functionality
- The `Schema::create` method is invoked to define the table structure.
- Inside the closure, a `Blueprint` instance `$table` is used to specify the columns and their data types.
- The following columns are defined:
  - `id`: An auto-incrementing primary key for the table (big integer).
  - `meal_plan_id`: A foreign key that references the `meal_plans` table, implementing a cascading delete policy. This ensures that if a meal plan is deleted, all related meal plan days are also deleted.
  - `day_number`: An unsigned integer representing the specific day of the meal plan.
  - `timestamps()`: Automatically created `created_at` and `updated_at` timestamp fields to track record creation and last updates.
- A unique constraint is placed on the combination of `meal_plan_id` and `day_number`. This ensures that there cannot be duplicate entries for the same meal plan on the same day.

### down() Method
```php
public function down(): void
{
    Schema::dropIfExists('meal_plan_days');
}
```

#### Purpose
The `down()` method is responsible for removing the `meal_plan_days` table from the database if the migration is rolled back.

#### Parameters
- None.

#### Return Values
- None (void).

#### Functionality
- The `Schema::dropIfExists` method checks for the existence of the `meal_plan_days` table and drops it if found. This provides a safe way to revert the changes made by the `up()` method in case the migration needs to be undone.

## Conclusion
The `2025_04_05_070301_create_meal_plan_days_table.php` migration file is an important part of the database setup for the NutriPlan application. By defining the structure and relationships associated with meal plan days, it ensures data integrity and supports the application's functionality in managing meal plans effectively. This documentation serves both as a guide to understand the implementation details and as a reference for developers working with migrations within the Laravel framework.