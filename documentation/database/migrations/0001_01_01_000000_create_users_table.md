# Documentation: 0001_01_01_000000_create_users_table.php

Original file: `database/migrations/0001_01_01_000000_create_users_table.php`

# 0001_01_01_000000_create_users_table.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [up](#up)
  - [down](#down)

## Introduction
The file `0001_01_01_000000_create_users_table.php` is a migration script for the NutriPlan application, specifically designed to create the essential database tables required for user management. It establishes three tables: `users`, `password_reset_tokens`, and `sessions`. This migration is part of Laravel's built-in migration system and enables the application to manage database schema changes effectively.

The tables defined in this migration serve the following purposes:
- **users**: Stores user information, including authentication credentials.
- **password_reset_tokens**: Facilitates password recovery by storing tokens tied to user emails.
- **sessions**: Manages user sessions, allowing for tracking active sessions along with relevant metadata like IP address and user agent.

## Methods

### up
```php
public function up(): void
```
#### Purpose
The `up` method is responsible for initiating the migration, creating the necessary tables in the database.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `up` method utilizes Laravel's Schema Builder to create three tables: `users`, `password_reset_tokens`, and `sessions`. 

- **users** table creation:
  - `id`: Auto-incrementing primary key.
  - `name`: String column for storing the user's name.
  - `email`: String column for storing the user's email, which must be unique.
  - `email_verified_at`: Timestamp column to track when the user's email was verified (nullable).
  - `password`: String column to store the user's hashed password.
  - `rememberToken()`: Adds a nullable column for "remember me" sessions.
  - `timestamps()`: Adds `created_at` and `updated_at` timestamp columns.

- **password_reset_tokens** table creation:
  - `email`: String column that serves as the primary key, tying the reset token to the user's email.
  - `token`: String column to hold the password reset token.
  - `created_at`: Timestamp for when the reset token was created (nullable).

- **sessions** table creation:
  - `id`: String primary key for session identifiers.
  - `user_id`: Foreign key referencing the `users` table, can be null.
  - `ip_address`: Nullable string column to record the IP address associated with the session.
  - `user_agent`: Nullable text column to store the user agent string.
  - `payload`: Long text column to store serialized session data.
  - `last_activity`: Integer column to track the last activity timestamp, indexed for faster queries.

### down
```php
public function down(): void
```
#### Purpose
The `down` method reverses the actions performed by the `up` method, dropping the tables created during this migration.

#### Parameters
- None

#### Return Values
- None

#### Functionality
The `down` method calls `Schema::dropIfExists()` for each of the tables created in the `up` method. This ensures that if the migration needs to be reverted, the tables will be cleanly removed from the database:

- Drops the `users` table.
- Drops the `password_reset_tokens` table.
- Drops the `sessions` table.

This functionality provides a mechanism for rolling back changes made in the database schema, which is particularly useful during development or when updating the application's database structure.

## Conclusion
This migration file plays a crucial role in establishing the foundational user management system of the NutriPlan application. The properly structured tables facilitate efficient user authentication, password management, and session control, which are essential for any modern web application. By understanding the structure and purpose of this migration, developers can effectively manage and evolve the application's database schema over time.