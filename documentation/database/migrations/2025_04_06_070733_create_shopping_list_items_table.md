# Documentation: 2025_04_06_070733_create_shopping_list_items_table.php

Original file: `database/migrations/2025_04_06_070733_create_shopping_list_items_table.php`

# Migration Documentation for `2025_04_06_070733_create_shopping_list_items_table.php`

## Table of Contents
- [Introduction](#introduction)
- [Migration Methods](#migration-methods)
  - [up()](#up)
  - [down()](#down)

## Introduction
The file `2025_04_06_070733_create_shopping_list_items_table.php` is a migration script used in the NutriPlan PHP application, specifically built with the Laravel framework. Its primary purpose is to create the `shopping_list_items` database table which will hold various items that users can select for their shopping lists. This table facilitates the management of ingredients, quantity, and categorization, allowing users to organize their shopping more efficiently.

## Migration Methods

### up()
#### Purpose
The `up()` method is responsible for defining the structure of the `shopping_list_items` table when the migration is executed. This method establishes the table's columns, their data types, and integrity constraints.

#### Parameters
 This method does not accept any parameters.

#### Return Values
This method does not return any values.

#### Functionality
When the `up()` method is called, it performs the following actions:

- **Table Creation**: It creates a new table named `shopping_list_items`.
- **Column Definitions**:
  
  | Column Name        | Type         | Attributes                         |
  |--------------------|--------------|------------------------------------|
  | `id`               | BigInt      | Primary Key                        |
  | `shopping_list_id` | BigInt      | Foreign Key references `shopping_lists` table, on delete cascade |
  | `ingredient_id`    | BigInt      | Nullable Foreign Key references `ingredients` table, on delete set null |
  | `name`             | String      | Not Nullable                       |
  | `quantity`         | Decimal(8,2)| Nullable                           |
  | `unit`             | String(50)  | Nullable                           |
  | `category`         | String(100) | Nullable                           |
  | `is_custom`        | Boolean     | Defaults to true                   |
  | `is_purchased`     | Boolean     | Defaults to false                  |
  | `created_at`       | Timestamp   | Auto-managed by Laravel            |
  | `updated_at`       | Timestamp   | Auto-managed by Laravel            |

- **Foreign Key Constraints**:
  - The `shopping_list_id` column is enforced as a foreign key that links to the `shopping_lists` table, ensuring referential integrity. It will be automatically removed if the associated shopping list is deleted (cascade delete).
  - The `ingredient_id` column is also enforced as a foreign key linking to the `ingredients` table but will be set to `null` if the referenced ingredient is deleted.

### down()
#### Purpose
The `down()` method corresponds to undoing the actions performed by the `up()` method. It is used to revert the database to its previous state.

#### Parameters
This method does not accept any parameters.

#### Return Values
This method does not return any values.

#### Functionality
When the `down()` method is called, it performs the following action:
- **Table Dropping**: It removes the `shopping_list_items` table from the database if it exists. This action helps maintain database integrity, ensuring no orphaned entries remain.

## Conclusion
The migration file `2025_04_06_070733_create_shopping_list_items_table.php` serves a crucial role in establishing the structure of the shopping list items within the NutriPlan application. It ensures proper database design principles are followed by implementing foreign keys and defaults while cleanly providing a way to reverse migrations when necessary. This versatility is vital for developers maintaining or upgrading the database schema as the application evolves.