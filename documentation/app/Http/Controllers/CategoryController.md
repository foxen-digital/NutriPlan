# Documentation: CategoryController.php

Original file: `app/Http/Controllers/CategoryController.php`

# CategoryController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes](#routes)
- [Methods](#methods)
  - [index](#index)
  - [store](#store)
  - [show](#show)

## Introduction
The `CategoryController` is responsible for managing categories within the NutriPlan application. It provides functionality to list categories, create new categories, and display recipes within a specific category. The controller interacts with a variety of models, such as `Category` and `Recipe`, and utilizes Laravel's Inertia.js for rendering views. It focuses on providing endpoints that are critical for a user to manage and explore categorized recipes effectively.

## Routes
The following routes are associated with the `CategoryController`:

- `GET /categories` - Retrieves a list of categories that have at least one public or user-owned recipe.
- `POST /categories` - Creates a new category in the storage.
- `GET /categories/{category}` - Displays the recipes associated with a specific category.

## Methods

### index
```php
public function index(Request $request): InertiaResponse
```

#### Purpose
Fetches and displays a listing of categories filtered by the availability of public or user-owned recipes.

#### Parameters
- `Request $request`: The incoming HTTP request which contains user authentication details.

#### Return Values
- `InertiaResponse`: A response object containing the rendered view with the list of categories.

#### Functionality
- Retrieves the currently authenticated user.
- Queries the `Category` model to fetch categories, eager loading a count of related recipes based on visibility.
- Filters out categories with no recipes.
- Renders a view displaying the categories that have at least one visible recipe to the user.

### store
```php
public function store(StoreCategoryRequest $request): JsonResponse
```

#### Purpose
Stores a newly created category in the database.

#### Parameters
- `StoreCategoryRequest $request`: A validated request object containing the data necessary to create a new category.

#### Return Values
- `JsonResponse`: A JSON response containing the resource for the newly created category, along with an HTTP status code of 201 (Created).

#### Functionality
- Uses the validated input from the `StoreCategoryRequest` to create a new `Category` instance.
- Responds with a resource representation of the created category using `CategoryResource`.

### show
```php
public function show(Category $category, Request $request): InertiaResponse
```

#### Purpose
Displays recipes that belong to a specified category.

#### Parameters
- `Category $category`: The category for which recipes are to be displayed.
- `Request $request`: The incoming HTTP request containing user authentication details.

#### Return Values
- `InertiaResponse`: A response object rendering the view with the specified category's recipes.

#### Functionality
- Retrieves the authenticated user (if available).
- Queries the `Recipe` model to find recipes associated with the given category.
- Applies filtering to ensure only public or user-owned recipes are included.
- Adds an `is_favorited` flag for each recipe if the user is logged in, indicating whether the recipe is among the user's favorites.
- Renders a view showing the recipes related to the specified category, along with the category details.

## Summary
The `CategoryController` serves as a critical component in managing categories for recipes within the NutriPlan application. Each method within the controller is designed to facilitate different aspects of category management, from display and organization to creation and interaction with user data. By leveraging Laravel's powerful Eloquent ORM, Inertia.js for rendering, and well-structured routing, the controller provides a seamless experience for users engaging with the recipe database categorically.