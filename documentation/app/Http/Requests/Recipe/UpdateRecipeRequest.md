# Documentation: UpdateRecipeRequest.php

Original file: `app/Http/Requests/Recipe/UpdateRecipeRequest.php`

# UpdateRecipeRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: authorize](#method-authorize)
- [Method: rules](#method-rules)

## Introduction

The `UpdateRecipeRequest` class is part of the NutriPlan application and is responsible for handling incoming HTTP requests related to the updating of recipe resources. This class extends `Illuminate\Foundation\Http\FormRequest`, providing a mechanism for validating and authorizing the request data before it is processed by the controller. 

The purpose of this request class is to encapsulate the validation logic for updating a recipe, ensuring that all required fields are present and meet specified criteria while also confirming that the authenticated user has the necessary permissions to perform the update operation.

## Method: authorize

### Purpose
The `authorize` method checks if the authenticated user is authorized to update the specific recipe identified by the route parameter.

### Return Value
- **Type:** bool
- **Returns:** `true` if the user has permission to update the recipe; otherwise, `false`.

### Functionality
This method retrieves the user from the request context using `$this->user()` and checks their permissions using the `can` method. The permission to update is determined by checking if the user can perform the `update` action on the `recipe` object fetched from the route:

```php
public function authorize(): bool
{
    return $this->user()->can('update', $this->route('recipe'));
}
```

## Method: rules

### Purpose
The `rules` method defines the validation rules for the request data when updating a recipe. 

### Return Value
- **Type:** array
- **Returns:** An associative array where keys are the names of the request fields and values are arrays of validation rules.

### Functionality
This method returns a comprehensive set of validation rules which ensure the integrity and correctness of the data being submitted. The rules enforce constraints on various fields:

- `title`: Required, must be a string, and not exceed 255 characters.
- `description`: Optional, must be a string if present.
- `instructions`: Required and must be a string.
- `prep_time`, `cooking_time`, `servings`: Required integers, must be 1 or greater.
- `is_public`: Optional boolean.
- `categories`: Optional array of category IDs, each must exist in the `categories` table.
- `ingredients`: Required array, must contain at least one ingredient object with:
  - `ingredient_id`: Required, must exist in the `ingredients` table.
  - `amount`: Optional numeric value, must be non-negative.
  - `unit`: Optional string.
- `images`: Optional array of image files, each constrained to a maximum file size of 5 MB.

Here is the complete set of validation rules:

```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'instructions' => ['required', 'string'],
        'prep_time' => ['required', 'integer', 'min:1'],
        'cooking_time' => ['required', 'integer', 'min:1'],
        'servings' => ['required', 'integer', 'min:1'],
        'is_public' => ['boolean'],
        'categories' => ['nullable', 'array'],
        'categories.*' => ['exists:categories,id'],
        'ingredients' => ['required', 'array', 'min:1'],
        'ingredients.*.ingredient_id' => ['required', 'exists:ingredients,id'],
        'ingredients.*.amount' => ['nullable', 'numeric', 'min:0'],
        'ingredients.*.unit' => ['nullable', 'string'],
        'images' => ['nullable', 'array'],
        'images.*' => ['image', 'max:5120'], // 5MB max per image
    ];
}
```

In conclusion, `UpdateRecipeRequest` encapsulates the necessary validation and authorization logic for updating recipe resources within the NutriPlan application, offering a structured and clear method of ensuring that all incoming data is valid and that users have the required permissions to make the requested changes. This effectively enhances the application's security and ensures data consistency.