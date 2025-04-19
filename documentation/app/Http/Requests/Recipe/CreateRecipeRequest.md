# Documentation: CreateRecipeRequest.php

Original file: `app/Http/Requests/Recipe/CreateRecipeRequest.php`

# CreateRecipeRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Methods](#methods)
  - [authorize](#authorize)
  - [rules](#rules)

## Introduction
The `CreateRecipeRequest` class is part of the `App\Http\Requests\Recipe` namespace in the PHP-based application NutriPlan. This class extends the `FormRequest` class provided by Laravel, which is designed to manage HTTP requests. The purpose of this class is to handle the validation of incoming data for creating a new recipe. By encapsulating the request validation logic, it ensures that only valid data is passed through to the application's controller, helping to maintain data integrity and security.

## Class Overview
The `CreateRecipeRequest` class includes the following primary functionalities:
- Authorization check to determine if the user can create a recipe.
- Definition of validation rules for the incoming request data related to recipe creation.

## Methods

### authorize
```php
public function authorize(): bool
```
#### Purpose
The `authorize` method determines whether the user is authorized to make this request. 

#### Parameters
- None

#### Return Values
- Returns a boolean value:
  - `true`: The user is authorized to create a recipe.
  - `false`: The user is not authorized (note: currently, this method always returns `true`).

#### Functionality
In its current implementation, the `authorize` method grants permission unconditionally (always returning `true`). In a production environment, this method can be adapted to include more sophisticated authorization logic based on user roles or permissions.

---

### rules
```php
public function rules(): array
```
#### Purpose
The `rules` method defines the validation rules for the incoming request data when creating a recipe.

#### Parameters
- None

#### Return Values
- Returns an associative array where:
  - Keys are the names of the input fields.
  - Values are arrays that specify the validation rules for those fields.

#### Functionality
The `rules` method includes a comprehensive set of validation criteria for various fields of the recipe creation request. The rules are as follows:

| Field           | Rules                                          | Description                                              |
|------------------|------------------------------------------------|----------------------------------------------------------|
| `title`          | `required`, `string`, `max:255`               | The title of the recipe must be provided and is string, with a maximum length of 255 characters.|
| `description`    | `nullable`, `string`                           | The recipe description is optional and should be a string if provided.|
| `instructions`    | `required`, `string`                          | Instructions must be provided and are required.         |
| `prep_time`      | `required`, `integer`, `min:1`               | Preparation time is required and must be an integer greater than or equal to 1. |
| `cooking_time`   | `required`, `integer`, `min:1`               | Cooking time is required and must be an integer greater than or equal to 1. |
| `servings`       | `required`, `integer`, `min:1`               | The number of servings is required and must be an integer greater than or equal to 1. |
| `is_public`      | `boolean`                                    | Specifies whether the recipe is public, can be `true`, `false`, or `null`. |
| `categories`      | `nullable`, `array`                          | An optional array of categories that the recipe belongs to. |
| `categories.*`    | `exists:categories,id`                       | Each category ID in the array must exist in the categories table. |
| `ingredients`     | `required`, `array`, `min:1`                | An array of ingredients must be provided, with at least one ingredient. |
| `ingredients.*.ingredient_id`  | `required`, `exists:ingredients,id` | Each ingredient must have a valid ID that exists in the ingredients table. |
| `ingredients.*.amount` | `nullable`, `numeric`, `min:0`        | The amount of the ingredient is optional and must be a non-negative number if provided. |
| `ingredients.*.unit`    | `nullable`, `string`                   | The unit of measurement for the amount is optional and should be a string if provided. |
| `images`         | `nullable`, `array`                         | An optional array of images for the recipe. |
| `images.*`       | `image`, `max:5120`                        | Each image must be a valid image type and should not exceed 5MB (5120 KB). |

This method thus ensures that the incoming data adheres to specified formats and constraints, mitigating potential issues related to data handling in the application.

## Conclusion
The `CreateRecipeRequest` class is an essential component for managing recipe creation requests in the NutriPlan application. By encapsulating validation logic within this class, developers can ensure that only valid, well-structured data is processed in the application. This class not only streamlines the handling of requests but also enhances the overall robustness and security of the application.