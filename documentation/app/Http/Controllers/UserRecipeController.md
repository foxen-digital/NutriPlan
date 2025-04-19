# Documentation: UserRecipeController.php

Original file: `app/Http/Controllers/UserRecipeController.php`

# UserRecipeController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes Handled by UserRecipeController](#routes-handled-by-userrecipecontroller)
- [Methods](#methods)
  - [index](#index)

## Introduction
The `UserRecipeController` is a controller class in the NutriPlan PHP application responsible for managing recipe listings associated with a specific user. It facilitates the retrieval and display of recipes based on user-specific criteria, including ownership and filtering by category. The controller leverages Laravel's Eloquent ORM for efficient database interactions and Inertia.js for rendering the frontend views.

## Routes Handled by UserRecipeController
The `UserRecipeController` handles the following route:
- `GET /users/{user}/recipes` - Displays a list of recipes for a specified user.

### Route Parameters
| Parameter | Description                       |
|-----------|-----------------------------------|
| `user`    | The authenticated user whose recipes are being retrieved. |

## Methods

### index
```php
public function index(Request $request, User $user): Response
```

#### Purpose
The `index` method retrieves a listing of recipes belonging to a specific user. It checks if the currently authenticated user is the owner, applies necessary filters, and determines recipe visibility based on ownership.

#### Parameters
| Parameter   | Type   | Description                                                  |
|-------------|--------|--------------------------------------------------------------|
| `$request`  | `Request` | An instance of the HTTP request, used to access query parameters and user session data. |
| `$user`     | `User`  | The user model instance for whom recipes are being retrieved. |

#### Return Value
The method returns an instance of `Response`, rendered using Inertia.js to display the user’s recipes in a frontend view.

#### Functionality
1. **User Validation**: The method begins by validating if the currently authenticated user is the same as the user whose recipes are being requested.
2. **Query Construction**: A query for `Recipe` models is constructed with the following features:
   - Eager loading of related `user` and `categories` models.
   - Filtering recipes by the `user_id`.
   - Ordering recipes in descending order based on creation date.
   
3. **Filtering by Category**: If a `category` is provided in the request, the query is further refined to only include recipes that belong to the specified category.
4. **Visibility Control**: If the current user is not the recipe owner, the query limits results to only include public recipes (those with `is_public` set to true).
5. **Pagination**: The recipes are paginated to limit results per page, with a default of 12 recipes per page.
6. **Enhancing Recipe Data**: The method transforms the recipe collection, adding an `is_favorited` attribute to each recipe, indicating if the current user has favorited that recipe.
7. **Response Rendering**: Finally, the method returns an Inertia response rendering the 'UserRecipes' component, passing the recipes, filter criteria, user details, and the ownership flag to the frontend.

### Example
```sh
GET /users/1/recipes?category=2
```
This request fetches a paginated list of recipes for the user with ID 1, filtered to those belonging to category ID 2.

---

This documentation provides a comprehensive overview of the `UserRecipeController` class, aiming to help developers understand its purpose, methods, and the underlying mechanics at play. The structure follows best practices in PHP documentation, ensuring clarity and ease of use.