# Documentation: StoreMealPlanRecipeRequest.php

Original file: `app/Http/Requests/StoreMealPlanRecipeRequest.php`

# StoreMealPlanRecipeRequest Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [authorize](#authorize)
3. [rules](#rules)
4. [failedAuthorization](#failedauthorization)

## Introduction
The `StoreMealPlanRecipeRequest` class is part of the HTTP requests in the NutriPlan PHP application. This class extends the `FormRequest` class from the Laravel framework and is used to handle incoming requests for storing recipes in meal plans. Its primary role is to authorize users for making specific requests based on their permissions and validate the data provided in the requests. By structuring the request handling logic in a dedicated class, the code maintains cleaner controllers and centralizes validation logic.

## authorize
```php
public function authorize(): bool
```
### Purpose
The `authorize` method determines whether the authenticated user has permission to update the specified meal plan.

### Parameters
- **None**

### Return Values
- Returns `true` if the user has permission to update the meal plan.
- Returns `false` if the user is not authorized.

### Functionality
1. The method retrieves the `meal_plan_id` from the request input.
2. It checks if the `meal_plan_id` exists; if not, it returns `false`.
3. It attempts to find a `MealPlan` instance using the provided `meal_plan_id`.
4. If the meal plan does not exist, it returns `false`.
5. Finally, it checks whether the authenticated user is allowed to update the meal plan using Laravel's Gate facade.
6. If the user is authorized, it returns `true`; otherwise, it returns `false`.

## rules
```php
public function rules(): array
```
### Purpose
The `rules` method defines the validation rules that apply to the incoming request data.

### Parameters
- **None**

### Return Values
- Returns an array containing validation rules.

### Functionality
This method specifies the validation requirements for the following fields:
| Field Name     | Validation Rules               | Description                                   |
|----------------|--------------------------------|-----------------------------------------------|
| `meal_plan_id` | `required|exists:meal_plans,id` | Must be provided and exist in the `meal_plans` table. |
| `recipe_id`    | `required|exists:recipes,id`    | Must be provided and exist in the `recipes` table.      |
| `scale_factor` | `nullable|numeric|min:0.01|max:100` | Can be omitted, but if provided, must be a numeric value within the specified range. |

These rules ensure that the incoming data adheres to the application's expectations before any further processing occurs.

## failedAuthorization
```php
protected function failedAuthorization(): void
```
### Purpose
The `failedAuthorization` method handles the scenario where a user is not authorized to perform the intended action.

### Parameters
- **None**

### Return Values
- The method does not return any values but throws an `AuthorizationException` if authorization fails under certain conditions.

### Functionality
1. This method overrides the default behavior in the `FormRequest` class.
2. It checks if `meal_plan_id` is present and whether a `MealPlan` with that ID exists.
3. If both conditions are satisfied, it calls the parent `failedAuthorization` method, which throws an `AuthorizationException`.
4. If either condition fails, it allows the validation to handle the response. This means that authorization exceptions will only be thrown if the request could potentially pass validation, preventing unnecessary exceptions in cases where the data is invalid.

By using this method, the class ensures that users are only informed of permission issues when they provide a valid meal plan ID, leading to a more user-friendly error handling process. 

---

This documentation serves as a guide for developers to understand the purpose, parameters, return values, and functionalities of the `StoreMealPlanRecipeRequest` class. This will help enhance code maintainability and facilitate future development.