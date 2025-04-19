# Documentation: 2025_03_22_130859_add_source_fields_to_recipes_table.php

Original file: `database/migrations/2025_03_22_130859_add_source_fields_to_recipes_table.php`

# Add Source Fields to Recipes Table Documentation

## Table of Contents
- [Introduction](#introduction)
- [Migration Class Overview](#migration-class-overview)
  - [Method: up()](#method-up)
  - [Method: down()](#method-down)

## Introduction

The `2025_03_22_130859_add_source_fields_to_recipes_table.php` file is a migration script in a Laravel-based PHP application designed to modify the existing `recipes` table in the database. It introduces additional fields aimed at enhancing the data structure associated with recipes by adding sourcing information. Specifically, this migration includes fields for a URL, an author, and an array of images, accommodating improved recipe documentation and potential integrations with external resources.

## Migration Class Overview

The class extends the `Migration` base class provided by the Laravel framework, allowing for a structured and version-controlled approach to modifying database tables. The migration consists of two main methods: `up()` and `down()`, which are responsible for applying and reversing the migration, respectively.

### Method: up()

```php
public function up(): void
{
    Schema::table('recipes', function (Blueprint $table): void {
        $table->string('url')->nullable()->after('description');
        $table->string('author')->nullable()->after('url');
        $table->json('images')->nullable()->after('instructions');
    });
}
```

#### Purpose
The `up()` method is invoked when the migration is run. Its purpose is to define modifications to the `recipes` table by adding new columns.

#### Parameters
- **None**: This method does not accept any parameters.

#### Return Values
- **void**: This method does not return any value.

#### Functionality
- The `up()` method uses Laravel's Schema Builder to modify the `recipes` table.
- New fields are added:
  - `url`: A nullable string field intended to store a link to the original source of the recipe.
  - `author`: A nullable string field for the author's name who created or contributed to the recipe.
  - `images`: A nullable JSON field designed to hold an array of image URLs for the recipe, which is beneficial for displaying multiple images in the app.

The `after()` method specifies the position of the new column relative to existing columns.

### Method: down()

```php
public function down(): void
{
    Schema::table('recipes', function (Blueprint $table): void {
        $table->dropColumn(['url', 'author', 'images']);
    });
}
```

#### Purpose
The `down()` method provides functionality to reverse the changes made by the `up()` method. This is particularly useful for rolling back migrations if necessary.

#### Parameters
- **None**: This method does not require any parameters.

#### Return Values
- **void**: This method does not return any value.

#### Functionality
- The `down()` method also utilizes Laravel's Schema Builder to drop the columns that were added in the `up()` method.
- The `dropColumn()` method accepts an array of column names (`url`, `author`, `images`), ensuring that these fields are removed from the `recipes` table during a rollback.

## Conclusion
By implementing this migration, developers can enrich the `recipes` database table with essential metadata about the recipes, including the source URL, author details, and image representations. This enhancement facilitates a better user experience and provides clarity on recipe origins and visual representations in the application.

This migration script serves as a crucial tool for maintaining and evolving the application's data schema in a coherent and controlled manner.