# Documentation: 2025_04_14_213835_create_personal_access_tokens_table.php

Original file: `database/migrations/2025_04_14_213835_create_personal_access_tokens_table.php`

# 2025_04_14_213835_create_personal_access_tokens_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The file `2025_04_14_213835_create_personal_access_tokens_table.php` is a migration file for the Laravel framework, responsible for creating the `personal_access_tokens` table in the database. This table is integral for managing personal access tokens associated with users or other entities, allowing them to authenticate and authorize requests to the system. The structure defined in this migration supports key attributes such as token name, unique token string, abilities, timestamps, and expiration settings, thereby facilitating scalable access control across the application.

## Methods

### up
```php
public function up(): void
```
#### Purpose
The `up` method is executed when the migration is run. It creates the `personal_access_tokens` table with the necessary columns to store information about each personal access token.

#### Parameters
This method does not accept any parameters.

#### Return Values
This method does not return any value.

#### Functionality
The `up` method employs the `Schema` facade to define the structure of the `personal_access_tokens` table. The following columns are created:

| Column Name       | Type              | Description                                                     |
|-------------------|-------------------|-----------------------------------------------------------------|
| `id`              | Incrementing ID   | A unique auto-incrementing identifier for each token.         |
| `tokenable_type`  | String            | The type of the entity associated with the token (morph).     |
| `tokenable_id`    | Unsigned Big Integer | The ID of the entity associated with the token (morph).      |
| `name`            | String            | A human-readable name for the token.                          |
| `token`           | String (64 chars) | A unique string representing the personal access token.       |
| `abilities`       | Text              | A nullable list of abilities associated with the token.       |
| `last_used_at`    | Timestamp         | A nullable timestamp indicating when the token was last used. |
| `expires_at`      | Timestamp         | A nullable timestamp indicating when the token expires.       |
| `created_at`      | Timestamp         | A timestamp for when the record was created (auto-managed).   |
| `updated_at`      | Timestamp         | A timestamp for when the record was last updated (auto-managed).|

### down
```php
public function down(): void
```
#### Purpose
The `down` method is executed when the migration is rolled back. It removes the `personal_access_tokens` table from the database.

#### Parameters
This method does not accept any parameters.

#### Return Values
This method does not return any value.

#### Functionality
The `down` method utilizes `Schema::dropIfExists` to safely drop the `personal_access_tokens` table if it exists. This ensures that no errors occur if an attempt is made to revert this migration without the table being present.

---

This documentation provides a clear understanding of the `2025_04_14_213835_create_personal_access_tokens_table.php` file's purpose, methods, and functionality. By understanding these components, developers can effectively manage user authentication and authorization through personal access tokens within the Laravel application.