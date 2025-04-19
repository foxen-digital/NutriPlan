# Documentation: VerifyEmailController.php

Original file: `app/Http/Controllers/Auth/VerifyEmailController.php`

# VerifyEmailController Documentation

## Table of Contents
- [Introduction](#introduction)
- [__invoke Method](#__invoke-method)
- [Routes Handled](#routes-handled)

## Introduction
The `VerifyEmailController` is a pivotal part of the authentication system within the NutriPlan PHP application. This controller is responsible for managing email verification for users. When a user registers or requests email verification, they need to confirm their email address. This is crucial for ensuring the authenticity of the user and for enhancing security measures within the application. 

By ensuring that a user's email is verified, the application can provide confidence that interactions and transactions attributed to that user are legitimate.

## __invoke Method

```php
public function __invoke(EmailVerificationRequest $request): RedirectResponse
```

### Purpose
The `__invoke` method serves as the main entry point for handling email verification requests. It processes the request to mark the authenticated user's email address as verified or redirects the user if certain conditions are not met.

### Parameters
- **EmailVerificationRequest** `$request`: This parameter is an instance of the `EmailVerificationRequest` class provided by Laravel. It encapsulates the request for email verification and contains the necessary user data.

### Return Values
- Returns an instance of `RedirectResponse`, which indicates the outcome of the verification request and redirects the user accordingly.

### Functionality
1. **User Authentication Check**:
   - The method first checks if a user is authenticated. If not, it redirects the user to the `login` route.
   
   ```php
   if (!$request->user()) {
       return redirect()->route('login');
   }
   ```

2. **Email Verification Status Check**:
   - If the user is authenticated, it checks if their email is already verified using the `hasVerifiedEmail()` method. If verified, it redirects them to the `recipes.index` route with a query parameter indicating successful verification.
   
   ```php
   if ($request->user()->hasVerifiedEmail()) {
       return redirect()->intended(route('recipes.index', absolute: false).'?verified=1');
   }
   ```

3. **Marking Email as Verified**:
   - If the email is not verified, the method proceeds to mark the email as verified using the `markEmailAsVerified()` method. If successful, it triggers the `Verified` event to handle any additional actions defined by the application concerning verified users.
   
   ```php
   if ($request->user()->markEmailAsVerified()) {
       /** @var \Illuminate\Contracts\Auth\MustVerifyEmail $user */
       $user = $request->user();
       event(new Verified($user));
   }
   ```

4. **Redirect After Verification**:
   - Regardless of whether the email was already verified or just marked as such, finally, it redirects the user to the `recipes.index` route with a query parameter indicating that verification was completed successfully.
   
   ```php
   return redirect()->intended(route('recipes.index', absolute: false).'?verified=1');
   ```

## Routes Handled
The `VerifyEmailController` is linked to the following route as defined in the application's routing file:

```php
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)->name('verification.verify');
```
This route is invoked when a user clicks on the verification link sent to their email, containing a unique identifier and hash for security purposes. The `__invoke` method processes the verification based on this route.

By following the structure, this documentation outlines the functionality of the `VerifyEmailController`, providing clarity on its purpose and implementation to aid developers in working with the email verification system within the NutriPlan application.