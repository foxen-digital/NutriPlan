# Documentation: MealPlanCopyRequest.php

Original file: `app/Http/Requests/MealPlanCopyRequest.php`

# MealPlanCopyRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: authorize](#method-authorize)
- [Method: rules](#method-rules)

## Introduction
The `MealPlanCopyRequest.php` file contains the `MealPlanCopyRequest` class, a specialized request class in a Laravel-based PHP application. This class is responsible for handling HTTP requests related to the action of copying a meal plan. It is part of the request-validation layer of the application, ensuring that any incoming requests meet specific criteria set for the operation. The class also includes authorization logic to restrict access to only those users who have the necessary permissions to view the meal plan.

## Method: authorize
```php
public function authorize(): bool
```

### Purpose
The `authorize` method determines whether the user making the request is authorized to perform this action. In this case, it checks if the user has permission to view the specified meal plan.

### Parameters
- **None**

### Return Values
- **bool**: Returns `true` if the user is authorized to view the meal plan, `false` otherwise.

### Functionality
1. The method retrieves the `mealPlan` parameter from the route using `$this->route('mealPlan')`.
2. It then uses Laravel's `Gate` facade to check permissions. Specifically, it calls `Gate::allows('view', $mealPlan)`, which verifies if the user is permitted to perform the 'view' action on the identified meal plan.
3. If the user is authorized, it returns `true`; if not, it returns `false`.

## Method: rules
```php
public function rules(): array
```

### Purpose
The `rules` method defines the validation rules applied to the incoming request. These rules specify what data is required and its format.

### Parameters
- **None**

### Return Values
- **array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>**: Returns an associative array of validation rules for the request parameters.

### Functionality
The `rules` method returns an array with the following validation rules:
- `name`: 
  - Type: `nullable|string|max:255`
  - Description: This field is optional and, if provided, must be a string with a maximum length of 255 characters.
  
- `start_date`: 
  - Type: `required|date`
  - Description: This field is mandatory and must be a valid date. It cannot be null.
  
- `people_count`: 
  - Type: `nullable|integer|min:1|max:20`
  - Description: This field is optional; if provided, it must be an integer greater than or equal to 1 and less than or equal to 20.

These rules ensure that incoming requests conform to the required format and constraints, enhancing data integrity before the request is processed further in the application.

---

This documentation provides an overview of the `MealPlanCopyRequest` class, explaining its purpose, methods, and the accompanying validation rules for handling requests related to meal plan copying within a Laravel application. Understanding this structure aids developers in recognizing how authorization and validation fit into the request lifecycle.