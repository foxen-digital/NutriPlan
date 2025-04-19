# Documentation: PasswordController.php

Original file: `app/Http/Controllers/Settings/PasswordController.php`

# PasswordController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [edit](#edit)
  - [update](#update)
- [Routes](#routes)

## Introduction
The `PasswordController` class handles functionalities related to user password management within the NutriPlan application. This controller provides two primary functions: displaying the password settings page and updating the user's password. It is part of the `App\Http\Controllers\Settings` namespace and utilizes Laravel's Inertia.js for rendering views.

The controller ensures that password updates are secure by enforcing validation rules and leveraging Laravel's built-in hashing utilities. This file plays a crucial role in allowing users to manage their authentication credentials safely.

## Methods

### edit
```php
public function edit(Request $request): Response
```

**Purpose**: 
Displays the user's password settings page.

**Parameters**: 
- `Request $request`: An instance of the incoming HTTP request, containing user information and session status.

**Return Values**: 
- Returns an instance of `Inertia\Response` which represents the rendered component for the password settings page.

**Functionality**:
- The `edit` method checks whether the authenticated user needs to verify their email using the `MustVerifyEmail` contract.
- It retrieves the status message from the session, if available.
- The method then renders the `settings/Password` Inertia component, passing along information about email verification and any status message.

### update
```php
public function update(Request $request): RedirectResponse
```

**Purpose**:
Updates the user's password with the new provided password.

**Parameters**: 
- `Request $request`: An instance of the incoming HTTP request, containing the password data submitted by the user.

**Return Values**: 
- Returns a `RedirectResponse` that redirects the user back to the previous page.

**Functionality**:
- The `update` method begins by validating the incoming request:
  - `current_password`: Required and must match the user's current password.
  - `password`: Required, must comply with default password rules, and be confirmed.
  
- If validation passes, the user's password is updated:
  - It uses `Hash::make()` to hash the new password before storing it.
  - The update is executed on the currently authenticated user's model through the `user()` method.

- Finally, the method redirects back to the previous page, typically the password settings page.

## Routes
The `PasswordController` handles the following routes:
- `GET /settings/password` - Invokes the `edit` method to display the password settings page.
- `POST /settings/password` - Invokes the `update` method to process the password update request.

These routes are typically defined in the `routes/web.php` file as part of the application route definitions featuring middleware for authentication. 

This documentation serves as a guide for developers looking to understand the purpose and operation of the `PasswordController` within the NutriPlan application. By adhering to Laravel's conventions and using Inertia.js, this controller provides a seamless and secure experience for managing user passwords.