# Documentation: MealPlanRecipeController.php

Original file: `app/Http/Controllers/MealPlanRecipeController.php`

# MealPlanRecipeController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [store](#store)
  - [destroy](#destroy)
- [Routes](#routes)
- [Models](#models)

## Introduction
The `MealPlanRecipeController` class is part of the NutriPlan application, responsible for managing the relationship between meal plans and recipes. It provides functionalities to add recipes to a meal plan and to remove them when necessary. The controller interacts with the models `MealPlan` and `Recipe` while ensuring that the business logic adheres to established authorization rules.

This file is central to the meal planning feature of the application, allowing users to create and manage their meal plans effectively by associating recipes with them.

## Methods

### store
```php
public function store(StoreMealPlanRecipeRequest $request): JsonResponse
```

#### Purpose
The `store` method is responsible for adding a recipe to an existing meal plan. It validates the incoming request data and checks for existing associations before attaching a new recipe.

#### Parameters
| Parameter                    | Type                               | Description                                                                             |
|------------------------------|------------------------------------|-----------------------------------------------------------------------------------------|
| `$request`                   | `StoreMealPlanRecipeRequest`      | An instance of a custom request class that contains validation rules for the input data. |

#### Return Values
- Returns a `JsonResponse` indicating the success of the operation.

#### Functionality
1. The method begins by validating the incoming request through the `validated()` method.
2. It retrieves the meal plan using the `meal_plan_id` from the validated data.
3. It checks whether the specified recipe (identified by `recipe_id`) is already associated with the meal plan.
4. If the recipe is not already added, it attaches the recipe to the meal plan with the provided `scale_factor`.
5. The method then calculates the available servings for the newly added recipe and saves the updated information.
6. Finally, it returns a JSON response with a success message.

Example of usage:
```json
{
  "success": true,
  "message": "Recipe added to meal plan successfully."
}
```

### destroy
```php
public function destroy(MealPlan $mealPlan, Recipe $recipe): RedirectResponse
```

#### Purpose
The `destroy` method is used to remove a recipe from a specified meal plan, ensuring that the user is authorized to make this change.

#### Parameters
| Parameter | Type    | Description                                                                  |
|-----------|---------|------------------------------------------------------------------------------|
| `$mealPlan` | `MealPlan`   | The instance of the MealPlan model from which the recipe will be removed. |
| `$recipe`    | `Recipe` | The instance of the Recipe model that is to be removed from the meal plan. |

#### Return Values
- Returns a `RedirectResponse` indicating the outcome of the operation (success message).

#### Functionality
1. The method logs a debug message with the IDs of the meal plan and recipe being processed.
2. It authorizes the request using the authorization gate, ensuring that the user has permission to update the meal plan.
3. The specified recipe is then detached from the meal plan.
4. Lastly, it redirects the user back to the previous page with a success message.

Example of redirect message:
```plaintext
Recipe removed from meal plan successfully.
```

## Routes
The `MealPlanRecipeController` typically handles the following routes (this may vary based on the routing configuration in the application):

- **POST** `/meal-plans/{mealPlan}/recipes` - Calls the `store` method to add a recipe to a meal plan.
- **DELETE** `/meal-plans/{mealPlan}/recipes/{recipe}` - Calls the `destroy` method to remove a recipe from a meal plan.

## Models

### MealPlan
The `MealPlan` model represents a meal plan that can contain multiple recipes. 

#### Relationships
- **recipes()**: A many-to-many relationship with the `Recipe` model. This relationship defines the `recipes` associated with a specific meal plan.

### Recipe
The `Recipe` model represents a recipe that can be used in different meal plans.

#### Attributes
- `id`: The unique identifier for the recipe.
- `name`: The name of the recipe.
- `preparation_time`: The time required to prepare the recipe.

### Pivot Table
The many-to-many relationship between `MealPlan` and `Recipe` is represented through a pivot table (usually `meal_plan_recipe`), which may include additional attributes such as `scale_factor` to define the quantity of the recipe associated with the meal plan.

By documenting these details, developers can understand the role of the `MealPlanRecipeController`, how to utilize it effectively, and the significance of its interaction with the underlying models.