# Documentation: 2025_03_31_173821_add_is_public_to_recipes_table.php

Original file: `database/migrations/2025_03_31_173821_add_is_public_to_recipes_table.php`

# 2025_03_31_173821_add_is_public_to_recipes_table Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The `2025_03_31_173821_add_is_public_to_recipes_table.php` file contains a migration class that is responsible for modifying the `recipes` table in the database. Specifically, this migration adds a new column named `is_public`, which is a boolean type that indicates whether a recipe is public (i.e., accessible by all users) or not (i.e., private). The default value for this column is set to `false`. 

This migration plays a crucial role in the functionality of the NutriPlan application by allowing recipes to be marked as public or private, which can enhance user privacy and the control of shared content within the application.

## Methods

### up
```php
public function up(): void
```
#### Purpose
The `up` method is called when the migration is executed. It defines the changes that need to be made to the database schema.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `up` method performs the following actions:
1. It uses the `Schema` facade to modify the `recipes` table.
2. It calls the `table` method on the `Schema` facade, passing in the name of the table (`recipes`) and a closure that receives an instance of `Blueprint`.
3. Inside the closure, it adds a new column called `is_public` of the boolean type to the `recipes` table using the `boolean` method.
4. The `default(false)` setting ensures that when a new recipe is created, it will automatically have the `is_public` value set to `false`.
5. The `after('servings')` parameter specifies that the new column should be added after the existing `servings` column in the database table structure.

### down
```php
public function down(): void
```
#### Purpose
The `down` method is called when the migration is rolled back. It defines the actions that should revert the changes made by the `up` method.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `down` method performs the following actions:
1. It uses the `Schema` facade to modify the `recipes` table.
2. It calls the `table` method on the `Schema` facade, passing in the name of the table (`recipes`) and a closure that receives an instance of `Blueprint`.
3. Inside the closure, it drops the `is_public` column from the `recipes` table using the `dropColumn` method. This effectively rolls back the migration, removing the previously added column from the database schema.

This allows developers to maintain continuity and integrity of the database schema during the development and deployment process.

## Conclusion
This migration is a key part of the NutriPlan database structure, introducing the `is_public` column to manage recipe visibility. Properly executing the migration adds functionality that can enhance user interaction with the application, while rolling back the migration ensures that application stability is maintained when necessary.