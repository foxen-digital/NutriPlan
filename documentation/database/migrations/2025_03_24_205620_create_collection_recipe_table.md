# Documentation: 2025_03_24_205620_create_collection_recipe_table.php

Original file: `database/migrations/2025_03_24_205620_create_collection_recipe_table.php`

# 2025_03_24_205620_create_collection_recipe_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Migration Class: Create Collection Recipe Table](#migration-class-create-collection-recipe-table)
  - [Method: up](#method-up)

## Introduction
The file `2025_03_24_205620_create_collection_recipe_table.php` is a migration script in a Laravel PHP application. Its purpose is to create a many-to-many relationship between the `collection` and `recipe` entities. This migration is crucial for establishing a link between collections of recipes and individual recipe entries, thereby enabling richer data queries and operations within the application.

In Laravel, migrations are used to define and modify database schemas in a way that can be easily rolled back and managed. This specific migration creates a junction table named `collection_recipe` that helps in mapping multiple recipes to multiple collections.

## Migration Class: Create Collection Recipe Table
The migration is defined as an anonymous class that extends Laravel's `Migration` class. 

### Method: up
#### Purpose
The `up` method is responsible for defining the schema changes that need to be applied to the database when the migration is run. 

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `up` method configures the creation of the `collection_recipe` table using Laravel's Schema Builder. Here are the steps it performs:

1. **Table Creation**: 
   - The method invokes `Schema::create`, passing the name of the table `collection_recipe` and a closure that defines the structure of the table.
  
2. **Defining Foreign Keys**:
   - It adds two foreign key columns:
     - `collection_id`: A foreign key that references the `id` column on the `collections` table.
     - `recipe_id`: A foreign key that references the `id` column on the `recipes` table.
   - Both foreign keys are set to cascade on delete, meaning that if a referenced collection or recipe is deleted, all associated entries in the `collection_recipe` table will also be deleted automatically.

3. **Primary Key**:
   - It sets the composite primary key to the combination of the `collection_id` and `recipe_id` columns. This primary key uniquely identifies each record in the junction table and enforces the many-to-many relationship.

```php
public function up(): void
{
    Schema::create('collection_recipe', function (Blueprint $table): void {
        $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
        $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
        $table->primary(['collection_id', 'recipe_id']);
    });
}
```

### Summary Table of Columns Created
| Column Name     | Data Type     | Constraints                | Description                                      |
|------------------|---------------|----------------------------|--------------------------------------------------|
| collection_id    | foreignId     | References `collections(id)` | The ID of the collection associated with the recipe. |
| recipe_id        | foreignId     | References `recipes(id)`     | The ID of the recipe associated with the collection. |
| (Composite Key)  | composite key | `collection_id`, `recipe_id`  | Uniquely identifies each entry in the table.     |

This migration enhances the flexibility and organization of the recipe management system, making it easier to categorize recipes into collections and retrieve them efficiently.