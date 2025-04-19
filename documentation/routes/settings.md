# Documentation: settings.php

Original file: `routes/settings.php`

# `/home/mrdth/Development/NutriPlan/ai-recipe-thing/routes/settings.php` Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes](#routes)
  - [Profile Routes](#profile-routes)
  - [Password Routes](#password-routes)
  - [Appearance Route](#appearance-route)
  - [API Tokens Routes](#api-tokens-routes)

## Introduction
The `settings.php` file is responsible for defining the routes associated with user settings in the NutriPlan application. It manages various functionalities such as editing user profiles, managing passwords, adjusting settings related to appearance, and handling API tokens. All routes defined in this file require the user to be authenticated, ensuring that sensitive operations are performed securely.

## Routes
The routes are grouped under an authentication middleware, which means that only authenticated users can access them. Each route is associated with a specific controller that handles the respective HTTP requests.

### Profile Routes
#### `GET settings/profile`
- **Purpose:** Displays the profile edit form.
- **Parameters:** None
- **Return Value:** Renders the edit profile view.

```php
Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
```

#### `PATCH settings/profile`
- **Purpose:** Updates the user's profile information.
- **Parameters:** 
  - `request`: The request object containing user input for the profile fields.
- **Return Value:** Redirects to the profile edit view with a success message if updated successfully.

```php
Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
```

#### `DELETE settings/profile`
- **Purpose:** Deletes the user's profile.
- **Parameters:** None
- **Return Value:** Redirects to a confirmation page or home page, typically with a success message.

```php
Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
```

### Password Routes
#### `GET settings/password`
- **Purpose:** Displays the password change form.
- **Parameters:** None
- **Return Value:** Renders the change password view.

```php
Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
```

#### `PUT settings/password`
- **Purpose:** Updates the user's password.
- **Parameters:**
  - `request`: Contains the user's current password, new password, and confirmation.
- **Return Value:** Redirects back with a success message if the password is changed successfully.

```php
Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');
```

### Appearance Route
#### `GET settings/appearance`
- **Purpose:** Displays the appearance settings view.
- **Parameters:** None
- **Return Value:** Renders the appearance settings page using Inertia.

```php
Route::get('settings/appearance', function () {
    return Inertia::render('settings/Appearance');
})->name('appearance');
```

### API Tokens Routes
#### `GET settings/tokens`
- **Purpose:** Displays a list of user's API tokens.
- **Parameters:** None
- **Return Value:** Renders the tokens index page with the list of tokens.

```php
Route::get('settings/tokens', [ApiTokenController::class, 'index'])->name('settings.tokens.index');
```

#### `POST settings/tokens`
- **Purpose:** Creates a new API token for the user.
- **Parameters:**
  - `request`: Contains the necessary information to generate a new token.
- **Return Value:** Generally redirects to the tokens page with a success message if created successfully.

```php
Route::post('settings/tokens', [ApiTokenController::class, 'store'])->name('settings.tokens.store');
```

#### `DELETE settings/tokens/{tokenId}`
- **Purpose:** Deletes a specified API token.
- **Parameters:**
  - `tokenId`: The ID of the token to be deleted.
- **Return Value:** Redirects to the tokens page/confirmation page with a success message.

```php
Route::delete('settings/tokens/{tokenId}', [ApiTokenController::class, 'destroy'])->name('settings.tokens.destroy');
```

## Conclusion
The `settings.php` file plays a crucial role in managing user-specific settings within the NutriPlan application. By defining routes that handle profile information, password management, appearance settings, and API tokens, it ensures that users have a secure and organized experience when adjusting their settings after logging into the system. Each route is handled by a dedicated controller, aligning with the application’s MVC architecture, facilitating better maintainability and readability of the code.