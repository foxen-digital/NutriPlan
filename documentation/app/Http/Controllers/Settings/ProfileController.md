# Documentation: ProfileController.php

Original file: `app/Http/Controllers/Settings/ProfileController.php`

# ProfileController Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Methods](#methods)
   - [edit](#edit)
   - [update](#update)
   - [destroy](#destroy)
3. [Routes](#routes)

## Introduction
The `ProfileController` class is responsible for handling user profile settings within the NutriPlan application. Located in the `app/Http/Controllers/Settings` directory, this controller manages the user interface for editing profile information, updating user data, and deleting user accounts. The file utilizes Inertia.js to seamlessly render React components, making the user experience smoother by avoiding full page reloads.

## Methods

### edit
```php
public function edit(Request $request): Response
```
- **Purpose**: Displays the user's profile settings page.
- **Parameters**:
  - `Request $request`: The HTTP request instance containing user data and session information.
- **Return Value**: Returns an `Inertia\Response` object that renders the 'settings/Profile' view.
  
#### Functionality
- It checks if the authenticated user must verify their email and retrieves the current status message from the session.
- The view with user settings is rendered, utilizing the Inertia.js framework to create a single-page application feel.

### update
```php
public function update(ProfileUpdateRequest $request): RedirectResponse
```
- **Purpose**: Updates the user's profile information based on validated input.
- **Parameters**:
  - `ProfileUpdateRequest $request`: A custom request class that validates the incoming data for user profile updates.
- **Return Value**: Returns a `RedirectResponse` that redirects the user back to the profile edit page.
  
#### Functionality
- The method uses optional chaining (`?->`) to update the user instance with validated data from the request.
- If the email address is modified, it sets `email_verified_at` to `null`, requiring email re-verification.
- Finally, it saves the user data to the database and redirects back to the profile edit route.

### destroy
```php
public function destroy(Request $request): RedirectResponse
```
- **Purpose**: Deletes the user's profile and logs them out of the application.
- **Parameters**:
  - `Request $request`: The HTTP request instance containing the user's current session and password.
- **Return Value**: Returns a `RedirectResponse` that redirects the user to the home page.

#### Functionality
- This method first validates the current user's password to ensure it is correct before proceeding with deletion.
- It logs the user out using Laravel's authentication facade.
- The user's account is then deleted from the database, followed by invalidating and regenerating the session token for security purposes.
- Finally, the user is redirected to the application root.

## Routes
The `ProfileController` is responsible for handling the following routes:

- **GET** `/profile/edit`: Displays the profile settings page by invoking the `edit` method.
- **POST** `/profile/update`: Updates the user profile with data submitted via a form and invokes the `update` method.
- **DELETE** `/profile/destroy`: Deletes the user account and invokes the `destroy` method.

These routes are typically configured in the `routes/web.php` file of the Laravel application.

---

This documentation provides a clear overview of the `ProfileController`, detailing its purpose, functionalities, methods, and associated routes. It aims to assist developers in understanding the implementation and usage of the controller in the NutriPlan application.