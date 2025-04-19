# Documentation: RegisteredUserController.php

Original file: `app/Http/Controllers/Auth/RegisteredUserController.php`

# RegisteredUserController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [create](#create)
  - [store](#store)
- [Routes](#routes)
- [Conclusion](#conclusion)

## Introduction
The `RegisteredUserController` class is responsible for managing the user registration process within the NutriPlan application. It extends the base `Controller` class provided by Laravel and utilizes the Inertia.js library to render the registration view. This controller handles incoming registration requests, validates the user input, creates a new user in the database, and logs the user in. The purpose of this file is to encapsulate the logic related to user registration while maintaining a clean separation of concerns.

## Methods

### create
```php
public function create(): Response
```
#### Purpose
The `create` method is responsible for displaying the user registration page.

#### Parameters
- None

#### Return Values
- Returns a `Response` object representing the Inertia.js response to render the registration view.

#### Functionality
This method uses the Inertia.js library to render the 'auth/Register' component, which is the user interface for registration. It does not require any parameters, making it simple and straightforward.

### store
```php
public function store(Request $request): RedirectResponse
```
#### Purpose
The `store` method handles the incoming registration request from users. It validates the provided input and creates a new user in the database.

#### Parameters
- `Request $request`: An instance of the incoming request which contains the user data (name, email, password).

#### Return Values
- Returns a `RedirectResponse` redirecting the user to the route associated with the index of recipes after successful registration.

#### Functionality
1. **Validation**: 
   - The method first validates the incoming request data:
     - `name`: Required, must be a string, and cannot exceed 255 characters.
     - `email`: Required, must be a lowercase string, must be a valid email format, cannot exceed 255 characters, and must be unique in the users table.
     - `password`: Required, must be confirmed, and conforms to default password rules dictated by Laravel.

2. **User Creation**: 
   - If validation passes, a new user record is created in the database:
     - The `name` is taken from the request.
     - The `email` is taken from the request, and the password is hashed for security using Laravel's `Hash` facade.

3. **Event Dispatching**: 
   - The `Registered` event is dispatched, which can be listened to by other parts of the application to perform actions whenever a new user is registered (e.g., sending welcome emails).

4. **User Authentication**: 
   - The newly created user is authenticated using Laravel's `Auth::login($user)` method.

5. **Redirect**: 
   - Finally, the method redirects the authenticated user to the 'recipes.index' route.

## Routes
This controller corresponds to the following routes commonly associated within a Laravel application:

```php
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
```
- **GET /register**: Displays the registration form.
- **POST /register**: Processes the registration form submission.

## Conclusion
The `RegisteredUserController` is a crucial component of the NutriPlan application for handling user registrations. Its methods provide a clear and systematic approach to creating new user accounts while ensuring data integrity through validation and secure password handling. This documentation should help developers understand how to work with the registration functionality and integrate it effectively within the larger application framework.