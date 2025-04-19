# Documentation: 2025_04_02_154223_add_servings_available_to_meal_plan_recipes_table.php

Original file: `database/migrations/2025_04_02_154223_add_servings_available_to_meal_plan_recipes_table.php`

# 2025_04_02_154223_add_servings_available_to_meal_plan_recipes_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Migration Class](#migration-class)
  - [up() Method](#up-method)
  - [down() Method](#down-method)

## Introduction
The file `2025_04_02_154223_add_servings_available_to_meal_plan_recipes_table.php` is a migration script for a Laravel application that modifies an existing database table `meal_plan_recipe`. Its primary purpose is to add a new column, `servings_available`, which stores the number of servings available for each recipe in a meal plan. This enhancement improves the flexibility and functionality of meal planning features within the application.

## Migration Class

This migration is part of the Laravel framework's database migration feature, which allows developers to create and manage database schema changes in a version-controlled manner.

### up() Method

```php
public function up(): void
{
    Schema::table('meal_plan_recipe', function (Blueprint $table): void {
        $table->decimal('servings_available', 8, 2)->nullable()->after('servings');
    });
}
```

#### Purpose
The `up()` method is responsible for applying the migration, which includes altering the `meal_plan_recipe` table to add the `servings_available` column.

#### Parameters
- `none`

#### Return Values
- `void`

#### Functionality
- This method uses the `Schema` facade to access the database schema.
- It applies the `table` method to the `meal_plan_recipe` table, passing in a closure that receives the `Blueprint` object.
- Within the closure, it defines the new column:
  - Type: `decimal`
  - Name: `servings_available`
  - Precision: `8` (total digits)
  - Scale: `2` (digits after the decimal)
  - Nullable: `true`, which means the column can hold `NULL` values.
  - The new column is added `after('servings')`, indicating its position relative to the existing `servings` column. This helps in managing the database schema by maintaining a logical order of columns.

### down() Method

```php
public function down(): void
{
    Schema::table('meal_plan_recipe', function (Blueprint $table): void {
        $table->dropColumn('servings_available');
    });
}
```

#### Purpose
The `down()` method is responsible for reversing the changes made in the `up()` method. It ensures that the migration can be rolled back safely.

#### Parameters
- `none`

#### Return Values
- `void`

#### Functionality
- Similar to the `up()` method, this function utilizes the `Schema` facade to access the `meal_plan_recipe` table.
- It uses the `dropColumn` method to remove the `servings_available` column.
- This rollback capability is crucial for maintaining data integrity and allowing developers to revert unintended migrations without losing data or functionality.

### Summary
The migration defined in `2025_04_02_154223_add_servings_available_to_meal_plan_recipes_table.php` provides a structured way to enhance the functionality of the `meal_plan_recipe` table by introducing a new column for tracking the available servings. This documentation aids developers in understanding the purpose and execution of schema modifications in a Laravel environment.