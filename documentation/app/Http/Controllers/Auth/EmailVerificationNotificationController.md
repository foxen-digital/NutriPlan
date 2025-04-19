# Documentation: EmailVerificationNotificationController.php

Original file: `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`

# EmailVerificationNotificationController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: store](#method-store)
- [Routes Handled](#routes-handled)

## Introduction
The `EmailVerificationNotificationController` class is part of the authentication system in the NutriPlan PHP application. It is responsible for handling the process of sending email verification notifications to users who have not yet verified their email addresses. Specifically, it provides a method to send a new verification email when requested, and redirect users accordingly depending on their verification status.

## Method: store

### Purpose
The `store` method is responsible for sending a new email verification notification to the currently authenticated user if they have not already verified their email address. If the user has verified their email, they are redirected to the recipes index page.

### Parameters
- **Request $request**: An instance of the `Illuminate\Http\Request` class containing the incoming HTTP request data.

### Return Values
- **RedirectResponse**: The method returns an instance of `Illuminate\Http\RedirectResponse`, which allows the application to redirect the user to different locations based on their email verification status.

### Functionality
The method performs the following steps:
1. **Check Email Verification**: 
   - It checks if the authenticated user has already verified their email using the method `$request->user()?->hasVerifiedEmail()`. The null-safe operator (`?->`) ensures the code does not throw an error if the user is not authenticated.
   - If the user has verified their email, it redirects them to the intended URL using `redirect()->intended()`, which generally takes the user to the previously requested page. Here, it specifically targets the route `'recipes.index'` without an absolute URL.

2. **Send Verification Notification**:
   - If the user's email is not verified, the method calls `$request->user()?->sendEmailVerificationNotification()` to send a new email verification notification. The null-safe operator ensures that the method call only happens if the user is authenticated.

3. **Redirect Back**:
   - After attempting to send the notification, the method redirects back to the previous page (likely the verification request page) with a flash message indicating that the verification link has been successfully sent. The message is set via `back()->with('status', 'verification-link-sent')`.

```php
public function store(Request $request): RedirectResponse
{
    if ($request->user()?->hasVerifiedEmail()) {
        return redirect()->intended(route('recipes.index', absolute: false));
    }

    $request->user()?->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
}
```

## Routes Handled
The `EmailVerificationNotificationController` typically handles the following route in the application:

- **POST /email/verification-notification**: This route calls the `store` method and is responsible for triggering the email verification process for authenticated users who require it.

This route is typically defined in the `routes/web.php` file or similar, ensuring that whenever a user requests this endpoint, the `store` method in `EmailVerificationNotificationController` is executed.

```php
Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->name('verification.send');
```

---

This documentation is designed to provide a comprehensive understanding of the `EmailVerificationNotificationController`, facilitating developers to quickly grasp its functionality and intent within the NutriPlan application.