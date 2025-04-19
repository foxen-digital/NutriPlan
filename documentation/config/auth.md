# Documentation: auth.php

Original file: `config/auth.php`

# auth.php Configuration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Authentication Defaults](#authentication-defaults)
- [Authentication Guards](#authentication-guards)
- [User Providers](#user-providers)
- [Password Reset Configuration](#password-reset-configuration)
- [Password Confirmation Timeout](#password-confirmation-timeout)

## Introduction
The `auth.php` configuration file is a crucial component of the NutriPlan application's authentication system. It defines the authentication settings, including the guards, user providers, and password reset configurations. This file is utilized by the Laravel framework to manage user authentication effectively, allowing the application to authenticate users securely and handle password resets efficiently.

## Authentication Defaults
### Purpose
The "defaults" section specifies the default settings for authentication within the application.

### Configuration Structure

```php
'defaults' => [
    'guard' => env('AUTH_GUARD', 'web'),
    'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
],
```

### Parameters
- **guard**: Represents the authentication guard to be used. It retrieves its value from the environment variable `AUTH_GUARD`, defaulting to `'web'`.
- **passwords**: Defines the password reset broker used for users, fetched from the environment variable `AUTH_PASSWORD_BROKER`, defaulting to `'users'`.

### Functionality
This section establishes the base authentication settings for the application, setting the guard and the password broker that will be used throughout.

## Authentication Guards
### Purpose
This section allows configuration of various authentication guards utilized by the application. Authentication guards define how users are authenticated for various parts of the application.

### Configuration Structure

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],
```

### Parameters
- **web**: This guard represents web-based authentication.
    - **driver**: Specifies the driver for the guard, set to `'session'`, meaning it utilizes PHP sessions for storing user authentication state.
    - **provider**: Refers to the user provider to use for retrieving user records, which is set to `'users'`.

### Functionality
The defined guards are critical for managing user sessions, indicating how and where to store user authentication details. In this case, the web guard uses session storage and a user provider defined in the `providers` section.

## User Providers
### Purpose
User providers define how user information is retrieved from the application’s data storage. This section allows multiple user definitions to accommodate different authentication needs.

### Configuration Structure

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => env('AUTH_MODEL', App\Models\User::class),
    ],
],
```

### Parameters
- **users**: Represents a user provider set up for the application.
    - **driver**: Indicates the retrieval method, set to `'eloquent'` for working with Eloquent ORM.
    - **model**: Specifies the User model class which defaults to `App\Models\User`, retrieved from the environment variable `AUTH_MODEL`.

### Functionality
User providers are essential for fetching user data from the database. By default, this configuration uses Laravel's Eloquent ORM to manage user records and can easily be extended to include additional user providers if necessary.

## Password Reset Configuration
### Purpose
This section handles password reset options, specifying how tokens are generated, stored, and validated. It is vital for enhancing application security during the password recovery process.

### Configuration Structure

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

### Parameters
- **users**: Represents the password reset configuration for users.
    - **provider**: Maps to the user provider (`users`), ensuring the correct retrieval method is employed during reset processes.
    - **table**: Refers to the database table used for storing password reset tokens, defaulting to `'password_reset_tokens'`.
    - **expire**: Defines the time duration (in minutes) that a reset token remains valid, set to `60`.
    - **throttle**: Indicates the wait time (in seconds) before a user can request additional reset tokens, also set to `60` seconds.

### Functionality
This configuration enhances security by limiting the lifespan of password reset tokens and the frequency with which users can generate them, helping to prevent misuse and protect user accounts.

## Password Confirmation Timeout
### Purpose
This setting controls the timeout period for the password confirmation dialogue, ensuring that users re-enter their credentials after a period of inactivity.

### Configuration Structure

```php
'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
```

### Parameters
- **password_timeout**: Determines the duration (in seconds) before the password confirmation window expires. The default value is set to `10800` seconds (3 hours), derived from the environment variable `AUTH_PASSWORD_TIMEOUT`.

### Functionality
This timeout feature is aimed at enhancing user security by prompting users to confirm their identity if they have navigated away or have been inactive for an extended period, thereby reducing the risk of unauthorized access.

---

This documentation provides a comprehensive overview of the `auth.php` configuration file in the NutriPlan application, detailing its purpose, structure, and functionality for developers looking to understand and modify the authentication settings.