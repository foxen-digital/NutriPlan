# Documentation: MealPlanCopyController.php

Original file: `app/Http/Controllers/MealPlanCopyController.php`

# MealPlanCopyController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: __invoke](#method__invoke)
- [Routes Handled](#routes-handled)
- [Models Used](#models-used)

## Introduction
The `MealPlanCopyController` is a controller class within the NutriPlan application that handles the logic for copying meal plans. It is responsible for accepting requests to duplicate a specific meal plan belonging to the authenticated user, utilizing a service class to perform the copy operation and then redirecting the user to the newly created meal plan.

This controller leverages data validation through a custom request class and interacts with the MealPlan model and MealPlanCopyService to encapsulate the business logic of copying meal plans.

## Method: __invoke
```php
public function __invoke(MealPlanCopyRequest $request, MealPlan $mealPlan, MealPlanCopyService $copyService): RedirectResponse
```

### Purpose
The `__invoke` method is the main entry point of the `MealPlanCopyController`. It is called when the controller is invoked, allowing for a cleaner, single-method controller design.

### Parameters
- `MealPlanCopyRequest $request`: An instance of the custom request class that carries the validated data needed for copying a meal plan.
- `MealPlan $mealPlan`: The meal plan instance that is to be duplicated. The instance is automatically resolved by Laravel's route model binding.
- `MealPlanCopyService $copyService`: An instance of the service class responsible for the actual copy logic.

### Return Value
- Returns an instance of `RedirectResponse`, which represents an HTTP response that redirects the user to a specified route.

### Functionality
1. **Authentication Check**: The method retrieves the currently authenticated user using `Auth::user()`.
2. **Data Validation**: It collects and validates the data from the incoming request using `$request->validated()`.
3. **Copy Operation**: The method calls the `copy` method from `MealPlanCopyService`, passing the original meal plan, the authenticated user, and the validated data to create a new meal plan.
4. **Redirection**: After the meal plan is copied, the user is redirected to the detail page of the new meal plan using `redirect()->route('meal-plans.show', $newMealPlan)`, accompanied by a success message stored in the session.

## Routes Handled
The `MealPlanCopyController` typically handles a route that is intended to copy a meal plan. This is generally defined in the application's routes file (usually `routes/web.php`). 
A sample route definition may look like this:

```php
Route::post('/meal-plans/{mealPlan}/copy', MealPlanCopyController::class);
```

This route captures POST requests to copy a meal plan identified by `{mealPlan}`.

## Models Used

### MealPlan
The `MealPlan` model represents the meal plans stored in the database. Key attributes and relationships include:

| Attribute     | Description                                   |
|---------------|-----------------------------------------------|
| `id`          | Unique identifier for the meal plan.         |
| `user_id`     | Foreign key referencing the user who owns the meal plan. |
| `name`        | Name of the meal plan.                        |
| `description` | Description of the meal plan.                 |
| `created_at`  | Timestamp when the meal plan was created.    |
| `updated_at`  | Timestamp when the meal plan was last updated.|

**Relationships**:
- `User`: Represents the owner of the meal plan.
- Possible relationships with other models such as `Recipe`, `Ingredient`, etc., if applicable, to manage meal components.

This documentation aims to provide developers with an understanding of the structure and functionality of the `MealPlanCopyController`. Understanding these attributes, functions, and relationships is essential for maintaining and extending the application effectively.