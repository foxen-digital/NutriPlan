# Documentation: MealPlanController.php

Original file: `app/Http/Controllers/MealPlanController.php`

# MealPlanController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes Handled](#routes-handled)
- [Methods](#methods)
  - [index](#index)
  - [create](#create)
  - [store](#store)
  - [show](#show)
  - [edit](#edit)
  - [update](#update)
  - [destroy](#destroy)

## Introduction
The `MealPlanController` class is part of the Laravel application responsible for managing meal plans. It interacts with the `MealPlan` and `User` models to provide CRUD (Create, Read, Update, Delete) functionality for meal plans related to the authenticated user. This controller handles incoming requests, validates user input, and manages the response format (either JSON or HTML through Inertia.js), making it a crucial component of the meal planning feature in the system.

## Routes Handled
The `MealPlanController` handles the following routes:

| HTTP Method | Route                  | Action      |
|-------------|------------------------|-------------|
| GET         | `/meal-plans`          | index()     |
| GET         | `/meal-plans/create`   | create()    |
| POST        | `/meal-plans`          | store()     |
| GET         | `/meal-plans/{id}`     | show()      |
| GET         | `/meal-plans/{id}/edit`| edit()      |
| PUT/PATCH   | `/meal-plans/{id}`     | update()    |
| DELETE      | `/meal-plans/{id}`     | destroy()   |

## Methods

### index
```php
public function index(): Response|JsonResponse
```
#### Purpose
Displays a listing of meal plans for the authenticated user.

#### Parameters
- None.

#### Return Values
- Returns a `Response` object for Inertia views or a `JsonResponse` for API responses.

#### Functionality
The `index` method retrieves all meal plans associated with the currently authenticated user, sorts them first by whether they are past or future plans, and by start dates. If the request specifies a JSON format, it responds with a JSON object containing the meal plans; otherwise, it renders a view with the sorted meal plans.

### create
```php
public function create(): Response
```
#### Purpose
Shows the form for creating a new meal plan.

#### Parameters
- None.

#### Return Values
- Returns a `Response` object for the Inertia view rendering the creation form.

#### Functionality
This method simply returns an Inertia view for creating a new meal plan. It does not require any input parameters or additional processing.

### store
```php
public function store(Request $request): RedirectResponse
```
#### Purpose
Stores a newly created meal plan in the database.

#### Parameters
- `Request $request`: The incoming request instance containing the meal plan data.

#### Return Values
- Returns a `RedirectResponse` to redirect the user after the meal plan has been created.

#### Functionality
The `store` method validates the incoming data for the meal plan, ensuring that required fields are present and properly formatted. If validation passes, it creates a new meal plan associated with the authenticated user and also generates corresponding meal plan days. Finally, it redirects the user back to the meal plans index with a success message.

### show
```php
public function show(MealPlan $mealPlan): Response
```
#### Purpose
Displays the details of a specific meal plan.

#### Parameters
- `MealPlan $mealPlan`: The meal plan instance to display.

#### Return Values
- Returns a `Response` object for the Inertia view rendering the meal plan details.

#### Functionality
This method first checks if the authenticated user has permission to view the specified meal plan using policies. It then loads related recipes and meal assignments for that meal plan and fetches other available meal plans for the user to offer additional context. Finally, it renders the meal plan details in an Inertia view.

### edit
```php
public function edit(string $id): null
```
#### Purpose
Shows the form for editing a specified meal plan.

#### Parameters
- `string $id`: The identifier of the meal plan to edit.

#### Return Values
- Returns `null`.

#### Functionality
Currently, this method is not implemented and returns `null`. The intended functionality may involve fetching the specific meal plan and rendering an edit form.

### update
```php
public function update(Request $request, string $id): null
```
#### Purpose
Updates a specified meal plan in storage.

#### Parameters
- `Request $request`: The incoming request instance containing the updated meal plan data.
- `string $id`: The identifier of the meal plan to update.

#### Return Values
- Returns `null`.

#### Functionality
This method is also not implemented and returns `null`. It is expected to contain logic for validating and saving updates to the specified meal plan.

### destroy
```php
public function destroy(MealPlan $mealPlan): RedirectResponse
```
#### Purpose
Removes the specified meal plan from storage.

#### Parameters
- `MealPlan $mealPlan`: The meal plan instance to be deleted.

#### Return Values
- Returns a `RedirectResponse` to redirect the user after deletion.

#### Functionality
The `destroy` method first checks if the authenticated user has permission to delete the specified meal plan. If authorized, it removes the meal plan from the database and redirects the user back to the meal plans index with a success message.

## Summary
This `MealPlanController` provides the essential functionality for handling meal plans in the application. Its methods enable users to create, view, and delete meal plans while ensuring secure access through authorization checks. The controller effectively utilizes Laravel's robust features, including request validation, model relationships, and Inertia for rendering dynamic responses.