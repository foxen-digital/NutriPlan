# Documentation: PasswordResetLinkController.php

Original file: `app/Http/Controllers/Auth/PasswordResetLinkController.php`

# PasswordResetLinkController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [create](#create)
  - [store](#store)
- [Routes](#routes)

## Introduction
The `PasswordResetLinkController` class is part of the authentication feature in the NutriPlan application, specifically handling the process of password reset link requests. This controller is responsible for rendering the password reset request view and managing the logic needed to send reset links to users who have forgotten their passwords. By utilizing Laravel's built-in Password broker, this controller ensures that password reset requests are securely managed and handled in a user-friendly manner.

## Methods

### create
```php
public function create(Request $request): Response
```
#### Purpose
The `create` method is responsible for displaying the password reset link request page to the user.

#### Parameters
- `Request $request`: An instance of the incoming HTTP request.

#### Return Values
- `Response`: Returns an Inertia response rendering the 'auth/ForgotPassword' view along with any session status messages.

#### Functionality
This method retrieves any status messages from the session, which can inform the user of the result of a previous password reset link request. It then renders the 'ForgotPassword' Inertia component, providing a seamless experience for the user within a single-page application framework.

### store
```php
public function store(Request $request): RedirectResponse
```
#### Purpose
The `store` method processes incoming password reset link requests, validating user input and triggering the sending of the reset password link.

#### Parameters
- `Request $request`: An instance of the incoming HTTP request containing user input.

#### Return Values
- `RedirectResponse`: Redirects the user back to the previous location with a session status message indicating the outcome of the password reset link request.

#### Functionality
1. **Input Validation**: The method uses Laravel's validation facilities to ensure that the `email` field is present and contains a valid email format. If validation fails, a `ValidationException` is thrown and appropriate error messages are returned.
2. **Sending Reset Link**: Upon successful validation, the method calls `Password::sendResetLink` to initiate the process of sending the password reset link to the email provided by the user.
3. **Response Creation**: Finally, the user is redirected back to the previous page with a session message indicating that a reset link will be sent if the account exists. This message serves to reassure the user that the request has been processed without revealing any specific information about the account's existence for security reasons.

## Routes
This controller handles the following routes:

| Method | URI                     | Action                               |
|--------|------------------------|--------------------------------------|
| GET    | /forgot-password        | App\Http\Controllers\Auth\PasswordResetLinkController@create |
| POST   | /forgot-password        | App\Http\Controllers\Auth\PasswordResetLinkController@store  |

### Route Functionality
- **GET /forgot-password**: Displays the password reset request form.
- **POST /forgot-password**: Accepts an email address and processes the password reset link request.

This detailed documentation should help developers understand the purpose and functionality of the `PasswordResetLinkController` within the NutriPlan application. By following the outlined methods and their responsibilities, developers can efficiently manage password reset features within the application, ensuring a smooth and secure user experience.