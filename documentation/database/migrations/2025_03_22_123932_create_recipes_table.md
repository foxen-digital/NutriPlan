# Documentation: 2025_03_22_123932_create_recipes_table.php

Original file: `database/migrations/2025_03_22_123932_create_recipes_table.php`

# 2025_03_22_123932_create_recipes_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Method: up](#method-up)
- [Method: down](#method-down)

## Introduction
The file `2025_03_22_123932_create_recipes_table.php` is a migration file within the Laravel framework that is responsible for creating the `recipes` table in the database. Migrations are a type of version control for database schemas and allow developers to define and modify the structure of database tables programmatically. This particular migration establishes the necessary columns and relationships for storing recipe data, which is critical for the functionalities within the NutriPlan application, including recipe creation, modification, and retrieval.

---

## Class Overview
```php
return new class () extends Migration {
    // Class implementation
};
```
This migration class extends the base `Migration` class provided by Laravel, enabling it to implement the methods required to create and drop the `recipes` table.

---

## Method: up
```php
public function up(): void
```
### Purpose
The `up` method is invoked when the migration is executed, creating the `recipes` table as specified.

### Parameters
None

### Return Value
None

### Functionality
The `up` method contains a call to `Schema::create`, which takes two parameters: the name of the table (`recipes`) and a closure that defines the columns of the table using a `Blueprint` instance. Within this closure:
- `$table->id();`: Creates an auto-incrementing primary key named `id`.
- `$table->foreignId('user_id')->constrained()->cascadeOnDelete();`: Defines a foreign key `user_id` that references the `id` column on the `users` table, enforcing referential integrity. If a user is deleted, all their associated recipes will also be deleted automatically.
- `$table->string('title');`: A string column named `title` to store the name of the recipe.
- `$table->text('description')->nullable();`: A nullable text column for an optional recipe description.
- `$table->text('instructions');`: A required text column for storing detailed cooking instructions.
- `$table->integer('prep_time')->comment('Time in minutes');`: An integer column indicating preparation time in minutes, with an explanatory comment.
- `$table->integer('cooking_time')->comment('Time in minutes');`: An integer column indicating cooking time in minutes, with an explanatory comment.
- `$table->integer('servings');`: An integer column to denote the number of servings the recipe yields.
- `$table->timestamp('published_at')->nullable();`: A nullable timestamp column indicating when the recipe was published.
- `$table->timestamps();`: Adds `created_at` and `updated_at` timestamp columns for tracking when records are created and modified.

---

## Method: down
```php
public function down(): void
```
### Purpose
The `down` method is invoked when the migration is rolled back, dropping the `recipes` table from the database.

### Parameters
None

### Return Value
None

### Functionality
The `down` method calls `Schema::dropIfExists`, which safely drops the `recipes` table if it exists. This functionality is crucial for maintaining the integrity of the migration system, allowing developers to revert database changes when necessary.

---

## Summary
This migration file plays a vital role in the NutriPlan application by defining the structure and constraints of the `recipes` table in the database. By utilizing Laravel's migration system, developers can easily manage changes to the database schema in a safe and consistent manner. The `up` method organizes the creation of the table and its relationships, while the `down` method provides a straightforward way to revert these changes when needed. 

This documentation aims to clarify the purpose and functionality of the code contained within the migration file, providing valuable insight for existing and future developers working on the NutriPlan application.