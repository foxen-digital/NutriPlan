# Documentation: 2025_03_29_104038_create_recipe_user_favorites_table.php

Original file: `database/migrations/2025_03_29_104038_create_recipe_user_favorites_table.php`

# create_recipe_user_favorites_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
    - [up](#up)
    - [down](#down)
- [Conclusion](#conclusion)

## Introduction
The `2025_03_29_104038_create_recipe_user_favorites_table.php` file is a migration script used in the NutriPlan application, which is developed using the Laravel framework. The primary purpose of this migration is to create a database table named `recipe_user_favorites` that establishes a many-to-many relationship between users and their favorite recipes. This enables the application to efficiently manage and retrieve user-specific favorite recipes.

## Methods

### up
#### Purpose
The `up` method is responsible for creating the `recipe_user_favorites` table within the database when the migration is run. This is where the structure of the table, including its columns and any constraints, is defined.

#### Parameters
- None

#### Return Values
- None (void)

#### Functionality
- The method utilizes the `Schema::create` function to create a new database table.
- A `Blueprint` object is passed to the closure, allowing for the definition of the table's columns and constraints.
- The following columns are created:
  - `user_id`: A foreign key that references the `id` column in the `users` table. This column establishes the relationship to the User model. It contains a cascading delete behavior, which means that if a user is deleted, all their associated favorites will also be removed.
  - `recipe_id`: A foreign key that references the `id` column in the `recipes` table, establishing the relationship to the Recipe model. Similar to `user_id`, it also has cascading delete behavior.
  - `timestamps`: Laravel's automatic management of `created_at` and `updated_at` timestamps for the records in this table.
- The primary key of this table is a composite key consisting of both `user_id` and `recipe_id`. This ensures that a user can only have one entry per recipe in their favorites.

```php
public function up(): void
{
    Schema::create('recipe_user_favorites', function (Blueprint $table): void {
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
        $table->timestamps();

        $table->primary(['user_id', 'recipe_id']);
    });
}
```

### down
#### Purpose
The `down` method is responsible for reversing the actions taken in the `up` method; specifically, it drops the `recipe_user_favorites` table from the database when the migration is rolled back.

#### Parameters
- None

#### Return Values
- None (void)

#### Functionality
- The method calls `Schema::dropIfExists` to safely drop the `recipe_user_favorites` table if it exists. This is crucial for maintaining a clean database migration history and ensures that developers can manage database schema changes effectively.

```php
public function down(): void
{
    Schema::dropIfExists('recipe_user_favorites');
}
```

## Conclusion
The `create_recipe_user_favorites_table.php` migration plays a vital role in enabling users to manage their favorite recipes within the NutriPlan PHP application. By establishing a many-to-many relationship between users and recipes through the `recipe_user_favorites` table, this migration supports key functionality that enhances user experience. Understanding the methods within this file allows developers to maintain and extend the application's database schema effectively.