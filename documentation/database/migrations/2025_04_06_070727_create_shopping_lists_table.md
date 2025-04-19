# Documentation: 2025_04_06_070727_create_shopping_lists_table.php

Original file: `database/migrations/2025_04_06_070727_create_shopping_lists_table.php`

# `2025_04_06_070727_create_shopping_lists_table.php` Migration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The `2025_04_06_070727_create_shopping_lists_table.php` file is a migration script for the Laravel framework. It is responsible for creating the `shopping_lists` table in the database. This migration is part of the NutriPlan application, which manages user dietary preferences and meal planning. The `shopping_lists` table is intended to store lists of grocery items that users can utilize for their meal preparations. This documentation provides detailed insights into the methods defined in the migration.

## Methods

### up
```php
public function up(): void
```
- **Purpose**: The `up` method contains the logic that defines how the migration is applied, specifically, the creation of the `shopping_lists` table.
- **Parameters**: None
- **Return Values**: None

#### Functionality
This method utilizes the `Schema` facade to create a new table named `shopping_lists`. The table includes the following columns:

| Column Name | Data Type      | Attributes                                    |
|-------------|----------------|-----------------------------------------------|
| id          | BigInt         | Primary Key (auto-incrementing)               |
| user_id     | BigInt         | Foreign Key referencing the `users` table; on delete cascade |
| name        | String         | Represents the name of the shopping list, can store up to 255 characters |
| created_at  | Timestamp      | Automatically managed timestamp for creation  |
| updated_at  | Timestamp      | Automatically managed timestamp for updates    |

The `foreignId` method indicates that `user_id` creates a relation with the `id` column of the `users` table. The `onDelete('cascade')` clause ensures that if a user is deleted, all associated shopping lists will also be deleted.

### down
```php
public function down(): void
```
- **Purpose**: The `down` method defines the logic that reverts the migration by dropping the `shopping_lists` table.
- **Parameters**: None
- **Return Values**: None

#### Functionality
This method uses the `Schema` facade to check if the `shopping_lists` table exists and, if it does, drops it. This is beneficial for rolling back migrations when changes need to be reverted during the development process or when an application needs to be reset to a previous state.

```php
Schema::dropIfExists('shopping_lists');
```

This line ensures that if the `shopping_lists` table exists, it will be removed from the database, thereby reversing the actions taken in the `up` method.

## Conclusion
This migration file is a critical part of the database schema within the NutriPlan application. It establishes the frameworks for storing user-specific shopping lists efficiently and ensures relational integrity through the use of foreign keys. Proper understanding and management of this migration will enable seamless user experiences in managing shopping lists as part of dietary planning.