# Documentation: MealAssignmentController.php

Original file: `app/Http/Controllers/MealAssignmentController.php`

# MealAssignmentController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Route Handling](#route-handling)
- [Method Documentation](#method-documentation)
  - [store](#store)
  - [update](#update)
  - [toggleToCook](#togglestocook)
  - [destroy](#destroy)

## Introduction
The `MealAssignmentController` class is part of the `NutriPlan` PHP application, serving as a controller for managing meal assignments within a meal planning system. It facilitates the creation, updating, deletion, and toggling of meal assignments on specific meal plan days. This controller ensures that meal recipes are correctly assigned to days in a meal plan, tracks the number of servings available, and manages the cooking status for each meal assignment.

## Route Handling
The `MealAssignmentController` handles the following routes (assuming REST-like conventions):
- `POST /meal-assignments` to store a new meal assignment
- `PUT /meal-assignments/{mealAssignment}` to update an existing meal assignment
- `PATCH /meal-assignments/{mealAssignment}/toggle-to-cook` to toggle the cooking status of a meal assignment
- `DELETE /meal-assignments/{mealAssignment}` to remove a meal assignment

## Method Documentation

### store

```php
public function store(Request $request): RedirectResponse
```

**Purpose**: Creates a new meal assignment based on the provided request data.

**Parameters**:
- `Request $request`: The incoming HTTP request containing the meal assignment data.

**Return Values**:
- `RedirectResponse`: Redirects back to the previous page with a success or error message.

**Functionality**:
1. Validates the incoming request data for required fields: `meal_plan_day_id`, `meal_plan_recipe_id`, `servings`, and `to_cook`.
2. Begins a database transaction to ensure data integrity.
3. Retrieves the relevant `MealPlanDay` and `MealPlanRecipe` models.
4. Checks if the recipe is already assigned to the chosen meal plan day and if there are sufficient servings available to proceed.
5. Checks the cooking status based on day numbers or if explicitly set.
6. Creates and saves a new `MealAssignment` record.
7. Updates the number of available servings for the recipe.
8. Commits the transaction or rolls it back in case of any exceptions.

### update

```php
public function update(Request $request, MealAssignment $mealAssignment): RedirectResponse
```

**Purpose**: Updates an existing meal assignment's details.

**Parameters**:
- `Request $request`: The incoming HTTP request with updated meal assignment data.
- `MealAssignment $mealAssignment`: The meal assignment instance to update.

**Return Values**:
- `RedirectResponse`: Redirects back to the previous page with a success or error message.

**Functionality**:
1. Validates the incoming request data for updated servings and optional `to_cook` information.
2. Begins a transaction.
3. Checks if the change in servings is feasible based on available servings.
4. Updates the number of available servings in the recipe.
5. Applies the new values to the existing `MealAssignment` instance and saves it.
6. Commits the transaction or rolls it back in case of exceptions.

### toggleToCook

```php
public function toggleToCook(MealAssignment $mealAssignment): JsonResponse
```

**Purpose**: Toggles the cooking status of a specified meal assignment.

**Parameters**:
- `MealAssignment $mealAssignment`: The meal assignment instance whose cooking status will be toggled.

**Return Values**:
- `JsonResponse`: A JSON response indicating success or failure of the operation.

**Functionality**:
1. Toggles the `to_cook` boolean attribute of the meal assignment.
2. Saves the modified assignment.
3. Returns a JSON response with the new state and an appropriate message. In case of failure, it returns an error response.

### destroy

```php
public function destroy(MealAssignment $mealAssignment): RedirectResponse
```

**Purpose**: Deletes a meal assignment and appropriately updates the number of servings available.

**Parameters**:
- `MealAssignment $mealAssignment`: The meal assignment instance to delete.

**Return Values**:
- `RedirectResponse`: Redirects back to the previous page with a success or error message.

**Functionality**:
1. Begins a transaction to ensure that changes can be rolled back if any issue occurs.
2. Increases the available servings of the corresponding recipe.
3. Deletes the specified meal assignment.
4. If the assignment was marked as "to cook," it finds the next earliest assignment for that meal recipe and marks it as "to cook" if applicable.
5. Commits the transaction or rolls it back in case of exceptions.

## Conclusion
This documentation serves as a comprehensive guide for understanding the `MealAssignmentController` class, focusing on its role in managing meal assignments in the `NutriPlan` application. By clearly describing the routes and functionalities of its methods, developers can effectively utilize and modify the controller as needed within the application.