# Documentation: RecipePolicy.php

Original file: `app/Policies/RecipePolicy.php`

# RecipePolicy Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method Documentation](#method-documentation)
  - [viewAny](#viewany)
  - [view](#view)
  - [create](#create)
  - [update](#update)
  - [delete](#delete)

## Introduction
The `RecipePolicy.php` file defines an authorization policy for managing the operations related to `Recipe` objects in the NutriPlan application. This file is part of the policies that leverage Laravel's authorization system, helping to ensure that users have the appropriate permissions when interacting with recipes. It encapsulates the logic that determines if a user is allowed to perform actions such as viewing, creating, updating, or deleting a recipe.

## Method Documentation

### viewAny
```php
public function viewAny(?User $user): bool
```
- **Purpose**: Determines whether the user is permitted to view any recipes.
- **Parameters**:
  - `?User $user`: The user attempting to view the recipes. This parameter can be `null`, indicating a guest user.
- **Return Value**: Always returns `true`.
- **Functionality**:
  - This method allows all users, including unauthenticated ones, to view the list of recipes. It is structured to accommodate future potential restrictions but currently functions to allow all views.

### view
```php
public function view(?User $user, Recipe $recipe): bool
```
- **Purpose**: Authorizes the user to view a specific recipe.
- **Parameters**:
  - `?User $user`: The user attempting to view the recipe, which may also be `null`.
  - `Recipe $recipe`: The recipe instance that the user wishes to view.
- **Return Value**: Returns a boolean indicating whether the user can view the recipe.
- **Functionality**:
  - If the user is logged in and they own the recipe (`$user->id === $recipe->user_id`), they are granted access.
  - Public recipes are accessible by any user, thus allowing visibility to all. Conversely, private recipes are restricted to their owners.

### create
```php
public function create(User $user): bool
```
- **Purpose**: Authorizes the user to create a new recipe.
- **Parameters**:
  - `User $user`: The authenticated user attempting to create a recipe.
- **Return Value**: Returns `true` for all authenticated users.
- **Functionality**:
  - This method empowers all users to create recipes regardless of their roles or states, facilitating an open environment for creating various recipes.

### update
```php
public function update(User $user, Recipe $recipe): bool
```
- **Purpose**: Determines whether the user can update a specific recipe.
- **Parameters**:
  - `User $user`: The user attempting to update the recipe.
  - `Recipe $recipe`: The recipe instance that the user wishes to update.
- **Return Value**: Returns `true` if the user is the owner of the recipe; otherwise returns `false`.
- **Functionality**:
  - The update permission is strictly enforced by checking if the `$user` is the owner of the recipe. This prevents unauthorized changes to recipes by users who do not own them.

### delete
```php
public function delete(User $user, Recipe $recipe): bool
```
- **Purpose**: Authorizes the user to delete a specific recipe.
- **Parameters**:
  - `User $user`: The user who attempts to delete the recipe.
  - `Recipe $recipe`: The recipe instance that the user wishes to delete.
- **Return Value**: Returns `true` if the user is the owner of the recipe; otherwise returns `false`.
- **Functionality**:
  - The delete method parallels the update method in that it restricts the deletion of recipes to their respective owners, ensuring that recipes cannot be deleted by unauthorized users.

## Conclusion
The `RecipePolicy` class is crucial in managing user permissions in the NutriPlan application, specifically concerning recipe management. It ensures that operations such as view, create, update, and delete are duly authorized based on the user's role and ownership of the recipe, thereby maintaining the integrity and security of the application's data.