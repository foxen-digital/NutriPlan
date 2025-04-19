# Documentation: NewPasswordController.php

Original file: `app/Http/Controllers/Auth/NewPasswordController.php`

# NewPasswordController Documentation

## Table of Contents

1. [Introduction](#introduction)
2. [create method](#create-method)
3. [store method](#store-method)
4. [Routes](#routes)

## Introduction

The `NewPasswordController` is a key component of the authentication module in the NutriPlan application, specifically designed to handle password reset functionalities. This controller provides endpoints that serve the password reset view and process the submission of new passwords when a user has forgotten their previous password. It utilizes Inertia.js for rendering views and integrates with Laravel's built-in password reset functionalities, ensuring secure handling and validation of passwords.

## create method

### Purpose

The `create` method displays the password reset form to the user, which is rendered using Inertia.js.

### Parameters

- **`Request $request`**: An instance of the incoming HTTP request containing the user's email and token.

### Return Value

- **`Response`**: Returns an Inertia response that renders the password reset view.

### Functionality

This method accepts the incoming request, retrieves the `email` and `token` parameters, and uses Inertia to render the `auth/ResetPassword` view. The parameters are passed to the view to facilitate the password reset process for the user.

```php
public function create(Request $request): Response
{
    return Inertia::render('auth/ResetPassword', [
        'email' => $request->email,
        'token' => $request->route('token'),
    ]);
}
```

## store method

### Purpose

The `store` method processes the submission of a new password and attempts to reset the user’s password based on the provided token, email, and new password.

### Parameters

- **`Request $request`**: An instance of the incoming HTTP request with the following expected inputs:
  - `token`: Required password reset token.
  - `email`: Required user's email address.
  - `password`: Required new password (must match confirmation).

### Return Value

- **`RedirectResponse`**: Redirects the user to the login page upon successful password reset, or throws a `ValidationException` if the reset fails.

### Functionality

1. **Validation**: The method first validates the incoming request data. It ensures that:
   - The `token` is present.
   - The `email` is a valid email address.
   - The `password` is provided and is confirmed (matches the `password_confirmation`).

2. **Password Reset Process**: If validation passes, the method attempts to reset the user's password using the `Password::reset` method, which performs the following operations:
   - Updates the user's password by hashing it and storing it.
   - Generates a new `remember_token` for the user.
   - Fires a `PasswordReset` event.

3. **Response Handling**: Based on the outcome of the password reset process:
   - If successful (`Password::PasswordReset`), it redirects the user to the login screen with a success status message.
   - If unsuccessful, it throws a `ValidationException` containing error messages.

```php
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user) use ($request): void {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    if ($status == Password::PasswordReset) {
        return to_route('login')->with('status', __($status));
    }

    throw ValidationException::withMessages([
        'email' => [__($status)],
    ]);
}
```

## Routes

The `NewPasswordController` is typically associated with the following routes in the application:

- **GET** `/password/reset` 
  - Displays the password reset form (handled by the `create` method).
  
- **POST** `/password/reset` 
  - Processes the new password submission (handled by the `store` method).

These routes are usually defined in the `routes/web.php` file or automatically registered by Laravel's built-in authentication scaffolding.

This comprehensive overview of the `NewPasswordController` provides developers with a clear understanding of how this component functions within the NutriPlan application, its interactions with Laravel's authentication system, and the importance of keeping user data secure during password reset processes.