# Documentation: 2025_03_22_132036_add_slug_to_recipes_table.php

Original file: `database/migrations/2025_03_22_132036_add_slug_to_recipes_table.php`

# 2025_03_22_132036_add_slug_to_recipes_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Migration Class](#migration-class)
  - [up Method](#up-method)
  - [down Method](#down-method)

## Introduction
The file `2025_03_22_132036_add_slug_to_recipes_table.php` is a migration script created for the Laravel framework. Its primary purpose is to modify the existing `recipes` table in the database by adding a new column named `slug`. This column is intended to store a URL-friendly string that typically represents the title of a recipe, aiding in creating cleaner and more SEO-friendly URLs.

Migrations in Laravel are a way to manage database schema changes, allowing developers to easily update the structure of their database over time. This specific migration will ensure that each recipe has a unique identifier in the form of a slug, which can be used for routing purposes within the application.

## Migration Class

### up Method
#### Purpose
The `up` method is responsible for applying the changes defined in the migration. In this case, it adds a `slug` column to the `recipes` table.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `up` method utilizes the `Schema::table` method to modify the `recipes` table. The method accepts a closure that defines the changes to be made to the table. Within this closure, a new string column named `slug` is added using the `$table->string('slug')->after('title')->unique();` statement, which accomplishes the following:
- `string('slug')`: Defines a new column of type string.
- `after('title')`: Specifies that the `slug` column should be added after the existing `title` column in the table.
- `unique()`: Ensures that all values in the `slug` column will be unique across the `recipes` table, preventing duplicate entries.

```php
public function up(): void
{
    Schema::table('recipes', function (Blueprint $table): void {
        $table->string('slug')->after('title')->unique();
    });
}
```

### down Method
#### Purpose
The `down` method reverses the changes made in the `up` method. It serves as a rollback mechanism to remove the `slug` column from the `recipes` table if the migration needs to be undone.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `down` method also employs the `Schema::table` method and a closure. Within this closure, the `dropColumn` method is called on the `slug` column. This means if the migration is rolled back, the `slug` column will be removed from the `recipes` table.

```php
public function down(): void
{
    Schema::table('recipes', function (Blueprint $table): void {
        $table->dropColumn('slug');
    });
}
```

## Summary
The `2025_03_22_132036_add_slug_to_recipes_table.php` migration is a key component in the database management of the `NutriPlan` application. Through this migration, developers ensure that the recipes stored in the database contain a `slug` that enhances URL structuring and search engine optimization. The definitions and implementations of both the `up` and `down` methods provide clear pathways for applying and rolling back this migration, in line with best practices in Laravel development.