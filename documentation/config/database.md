# Documentation: database.php

Original file: `config/database.php`

# database.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Configuration Structure](#configuration-structure)
  - [Default Database Connection](#default-database-connection)
  - [Database Connections](#database-connections)
    - [SQLite Connection](#sqlite-connection)
    - [MySQL Connection](#mysql-connection)
    - [MariaDB Connection](#mariadb-connection)
    - [PostgreSQL Connection](#pgsql-connection)
    - [SQL Server Connection](#sqlsrv-connection)
  - [Migration Repository Table](#migration-repository-table)
  - [Redis Databases](#redis-databases)

## Introduction
The `database.php` file in the NutriPlan application serves as the central configuration file for database connectivity. It defines the default database connection and various database configurations supported by the Laravel framework. This file allows developers to specify database types, connection details, and various options that control how database interactions are handled within the application.

## Configuration Structure
The structure of `database.php` encompasses several key sections, including the specification of the default connection, definitions for multiple database connections, migration repository settings, and Redis configuration. 

### Default Database Connection
The default database connection is specified within the configuration. It determines which database will be used for operations if no alternate connection is provided in query execution.

```php
'default' => env('DB_CONNECTION', 'sqlite'),
```

- **Purpose**: Specifies the default database connection
- **Parameters**:
  - `env('DB_CONNECTION')`: The environment variable defining the default connection, falling back to 'sqlite'.
  
### Database Connections
This section defines the various database connections available in the application, including parameters for each database type.

#### SQLite Connection
```php
'sqlite' => [
    'driver' => 'sqlite',
    'url' => env('DB_URL'),
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'prefix' => '',
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    'busy_timeout' => null,
    'journal_mode' => null,
    'synchronous' => null,
],
```

- **Purpose**: Configuration for SQLite database.
- **Parameters**:
  - `driver`: Sets the database driver as 'sqlite'.
  - `url`: Connection URL from the environment.
  - `database`: Path to the SQLite database file, defaulting to `database.sqlite`.
  - `prefix`: Table prefix for database tables (optional).
  - `foreign_key_constraints`: Enables foreign key constraints if set to true.
  
#### MySQL Connection
```php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

- **Purpose**: Configuration for MySQL database.
- **Parameters**:
  - `driver`: Sets the driver as 'mysql'.
  - `host`: Host of the MySQL server (defaults to `127.0.0.1`).
  - `port`: Port for MySQL connection (defaults to `3306`).
  - `database`: Name of the database (defaults to `laravel`).
  - `username`: Database user name (defaults to `root`).
  - `password`: Database password (empty by default).
  - `unix_socket`: Specifies MySQL socket.
  - `charset` and `collation`: Character encoding options.
  - `strict`: Enables strict mode for SQL syntax.
  
#### MariaDB Connection
Configuration is similar to MySQL, allowing for MariaDB connections with the same parameters.

```php
'mariadb' => [
    'driver' => 'mariadb',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```
- **Purpose**: Configuration for MariaDB, reflecting similar structure as MySQL.

#### PostgreSQL Connection
```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
],
```

- **Purpose**: Configuration for PostgreSQL databases.
- **Parameters**:
  - `search_path`: Sets the search path for database schemas.
  - `sslmode`: Defines SSL connection mode.

#### SQL Server Connection
```php
'sqlsrv' => [
    'driver' => 'sqlsrv',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', 'localhost'),
    'port' => env('DB_PORT', '1433'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
],
```

- **Purpose**: Configuration for SQL Server databases.
