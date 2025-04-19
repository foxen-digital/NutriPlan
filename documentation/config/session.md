# Documentation: session.php

Original file: `config/session.php`

# session.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Configuration Options](#configuration-options)
  - [Driver](#driver)
  - [Lifetime](#lifetime)
  - [Expire on Close](#expire-on-close)
  - [Encrypt](#encrypt)
  - [Files](#files)
  - [Connection](#connection)
  - [Table](#table)
  - [Store](#store)
  - [Lottery](#lottery)
  - [Cookie](#cookie)
  - [Path](#path)
  - [Domain](#domain)
  - [Secure](#secure)
  - [HTTP Only](#http-only)
  - [Same Site](#same-site)
  - [Partitioned](#partitioned)

## Introduction

The `session.php` configuration file is a crucial component of the Laravel framework that handles the behavior of session management in applications. It allows developers to define how sessions should be stored, secured, and managed throughout their web application. Sessions are essential for maintaining state in web applications, enabling features like user authentication and data persistence between requests.

This file provides a centralized way to configure session-related options that dictate how session data is stored and managed, including the driver for session storage, expiration behavior, encryption, and cookie settings.

## Configuration Options

### Driver
- **Purpose**: Determines the default session driver used for incoming requests.
- **Parameter**: Retrieved through `env('SESSION_DRIVER', 'database')`.
- **Supported Options**: 
  - `file`
  - `cookie`
  - `database`
  - `apc`
  - `memcached`
  - `redis`
  - `dynamodb`
  - `array`
- **Functionality**: Sets the storage mechanism for sessions. The default is usually set to `database` for significant persistence.

### Lifetime
- **Purpose**: Specifies the duration (in minutes) that the session should remain active while idle.
- **Parameter**: Defined with `(int) env('SESSION_LIFETIME', 120)`.
- **Functionality**: If users do not interact with the session for the specified period, it will expire. An immediate expiration upon closing the browser can be set with the `expire_on_close` option.

### Expire on Close
- **Purpose**: Indicates if sessions should expire when the browser is closed.
- **Parameter**: Retrieved through `env('SESSION_EXPIRE_ON_CLOSE', false)`.
- **Functionality**: Setting this option to `true` means sessions will not persist after the browser is closed.

### Encrypt
- **Purpose**: Configures whether session data should be encrypted before being stored.
- **Parameter**: Accessed via `env('SESSION_ENCRYPT', false)`.
- **Functionality**: Automatically encrypts session data if enabled, enhancing data security at rest.

### Files
- **Purpose**: Specifies the file location for session storage when using the file driver.
- **Parameter**: Set to `storage_path('framework/sessions')`.
- **Functionality**: This path defines where the session files will be stored on the server.

### Connection
- **Purpose**: Defines which database connection is used when managing sessions with database or Redis drivers.
- **Parameter**: Retrieved through `env('SESSION_CONNECTION')`.
- **Functionality**: Matches the specified connection to ensure session data is stored in the intended database.

### Table
- **Purpose**: Allows the specification of the table used for storing sessions when the database driver is employed.
- **Parameter**: Set via `env('SESSION_TABLE', 'sessions')`.
- **Functionality**: Changes the default table used for storing session data within a database.

### Store
- **Purpose**: Identifies the cache store used when utilizing cache-driven session backends.
- **Parameter**: Retrieved through `env('SESSION_STORE')`.
- **Functionality**: Ensures coherence between the session and cache configurations.

### Lottery
- **Purpose**: Manages the chance that old sessions will be removed from storage.
- **Parameter**: Configured as an array with a default of `[2, 100]`.
- **Functionality**: Represents the odds of a session sweep occurring on a given request, with a higher likelihood of sessions being cleared as defined.

### Cookie
- **Purpose**: Allows customization of the session cookie name.
- **Parameter**: Accessed through `env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session')`.
- **Functionality**: The name can follow the pattern of the application name and is formatted to avoid common pitfalls associated with invalid cookie names.

### Path
- **Purpose**: Declares the cookie path where the session cookie is available.
- **Parameter**: Retrieved through `env('SESSION_PATH', '/')`.
- **Functionality**: By default, the cookie will be valid for the entire application.

### Domain
- **Purpose**: Configures the domain and subdomains available for the session cookie.
- **Parameter**: Set with `env('SESSION_DOMAIN')`.
- **Functionality**: Defines cookie accessibility across specified domains.

### Secure
- **Purpose**: Indicates if the session cookie will only be sent over HTTPS.
- **Parameter**: Accessed using `env('SESSION_SECURE_COOKIE')`.
- **Functionality**: Ensures security by allowing cookies to be transmitted only in secured connections.

### HTTP Only
- **Purpose**: Determines if the session cookie can be accessed via JavaScript.
- **Parameter**: Retrieved through `env('SESSION_HTTP_ONLY', true)`.
- **Functionality**: Improves security by making cookies inaccessible to client-side scripts.

### Same Site
- **Purpose**: Configures cookie behavior during cross-site requests to mitigate CSRF attacks.
- **Parameter**: Defined by `env('SESSION_SAME_SITE', 'lax')`.
- **Functionality**: Possible values include "lax", "strict", "none", and null, which determine how cookies behave with cross-origin requests.

### Partitioned
- **Purpose**: Configures whether the cookie is tied to the top-level site in cross-site contexts.
- **Parameter**: Accessed via `env('SESSION_PARTITIONED_COOKIE', false)`.
- **Functionality**: When true, allows the browser to maintain session isolation across different origins under certain conditions.


This documentation serves to give developers a comprehensive understanding of session management within a Laravel application, focusing on configuration settings that have a significant impact on session behavior, security, and persistence.