# Documentation: LoginRequest.php

Original file: `app/Http/Requests/Auth/LoginRequest.php`

# LoginRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [authorize](#authorize)
  - [rules](#rules)
  - [authenticate](#authenticate)
  - [ensureIsNotRateLimited](#ensureIsnotratelimited)
  - [throttleKey](#throttlekey)

## Introduction
The `LoginRequest` class is part of the authentication system within the NutriPlan application. It serves the purpose of handling user login requests by validating the credentials provided by the user, implementing rate-limiting to prevent abuse, and managing login attempts. The class extends `FormRequest`, which provides built-in validation and authorization features in Laravel. By centralizing the login logic within this request class, it promotes cleaner code and better separation of concerns in the application.

## Methods

### authorize
```php
public function authorize(): bool
```
- **Purpose**: Determine if the user is authorized to make the login request.
- **Parameters**: None.
- **Return Values**: 
  - Returns `true`, allowing all requests to pass authorization checks.
- **Functionality**: This method overrides the default `authorize` method from `FormRequest`. In this implementation, it returns `true` unconditionally, meaning all users can attempt to log in regardless of their status.

### rules
```php
public function rules(): array
```
- **Purpose**: Define the validation rules for the login request.
- **Parameters**: None.
- **Return Values**: 
  - Returns an array of validation rules for the request:
    ```php
    [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ]
    ```
- **Functionality**: This method specifies the requirements for the login fields. The `email` field must be a required string formatted as an email, while the `password` field must be a required string. If the inputs do not meet these criteria, a validation exception will be thrown.

### authenticate
```php
public function authenticate(): void
```
- **Purpose**: Attempt to authenticate a user using the provided credentials.
- **Parameters**: None.
- **Return Values**: None.
- **Functionality**: 
  - This method first calls `ensureIsNotRateLimited()` to check if the user has exceeded the login attempt limit.
  - It then uses `Auth::attempt()` to verify the user's credentials against the database. If the authentication fails, it logs the attempt by hitting the rate limiter and throws a validation exception with an error message.
  - If the authentication is successful, it clears any existing rate limit for the user's email.

### ensureIsNotRateLimited
```php
public function ensureIsNotRateLimited(): void
```
- **Purpose**: Verify whether the current login attempt is not exceeding rate limits.
- **Parameters**: None.
- **Return Values**: None.
- **Functionality**: 
  - This method checks if the number of login attempts associated with the user's email exceeds a predefined limit (5 attempts in this case).
  - If the limit is exceeded, it triggers a `Lockout` event and throws a validation exception with a message indicating how long the user must wait before trying again. The waiting time is calculated in seconds and rounded up to minutes for user-friendly errors.

### throttleKey
```php
public function throttleKey(): string
```
- **Purpose**: Generate a unique key for the rate-limiting throttle.
- **Parameters**: None.
- **Return Values**: 
  - Returns a string that serves as the throttle key, based on the email and the user's IP address.
- **Functionality**: 
  - The method combines the user’s email (converted to lowercase) with their IP address to ensure that rate-limiting is applied per user and per IP combination. The resulting key is processed through `Str::transliterate()` to ensure it complies with the expected format for the rate limiter.

---

This documentation is designed to provide a thorough understanding of the `LoginRequest` class, including its methods and their purposes within the broader context of application authentication in the NutriPlan project. By following this guide, developers can easily comprehend the code's functionality and how it fits into user login workflows.