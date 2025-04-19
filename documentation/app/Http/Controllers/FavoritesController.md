# Documentation: FavoritesController.php

Original file: `app/Http/Controllers/FavoritesController.php`

# FavoritesController Documentation

## Table of Contents
- [Introduction](#introduction)
- [index Method](#index-method)
  - [Parameters](#parameters)
  - [Return Values](#return-values)
  - [Functionality](#functionality)
- [Routes Handled](#routes-handled)
- [Relationship with Models](#relationship-with-models)

## Introduction
The `FavoritesController` is part of the NutriPlan application and is responsible for handling user interactions with their favorite recipes. This controller provides an endpoint for retrieving a list of recipes that users have marked as favorites. It utilizes the Laravel framework's Eloquent ORM for database interactions and Inertia for rendering the response. The main purpose of this controller is to enhance the user experience by allowing users to easily view, manage, and access their favorite recipes.

## index Method

### Purpose
The `index` method retrieves a paginated list of recipes that the authenticated user has marked as favorites. It enriches the recipe data by including associated user and category information.

### Parameters
- **Request $request**: An instance of the HTTP request class that contains user-specific data and input parameters.

### Return Values
- **Response**: An Inertia response rendering the 'Recipes/Favorites' view, containing the user's favorite recipes.

### Functionality
1. **Authentication**: The method retrieves the authenticated user from the request.
2. **Querying Favorites**: It accesses the user's favorites through the `favorites` relationship. 
   - It loads related data, including:
     - User's information (`id`, `name`, and `slug`).
     - Categories associated with each recipe, which are further enriched with a count of associated recipes, ordered by the count in descending order.
3. **Counting Ingredients**: Each favorite recipe is also augmented with a count of its associated ingredients.
4. **Pagination**: The results are paginated, returning 12 favorite recipes per page, with query string parameters carried over for pagination controls.
5. **Transforming Recipes**: Each recipe in the favorites collection is transformed to include an additional boolean attribute `is_favorited`. This indicates that these recipes are indeed favorites of the user.
6. **Rendering Response**: Finally, it returns an Inertia response with the paginated list of favorite recipes.

```php
public function index(Request $request): Response
{
    // Retrieve authenticated user
    $user = $request->user();

    // Get user's favorite recipes with related data and pagination
    $favorites = $user->favorites()
        ->with(['user:id,name,slug', 'categories' => function (Builder|BelongsToMany $query): void {
            $query->withCount('recipes')
                ->orderBy('recipes_count', 'desc');
        }])
        ->withCount('ingredients')
        ->paginate(12)
        ->withQueryString();

    // Add is_favorited flag to each recipe
    $favorites->getCollection()->transform(function (Recipe $recipe): Recipe {
        $recipe->is_favorited = true;
        return $recipe;
    });

    // Return Inertia response
    return Inertia::render('Recipes/Favorites', [
        'favorites' => $favorites,
    ]);
}
```

## Routes Handled
The `FavoritesController` is expected to handle the following route:

- **GET /favorites**: This route maps to the `index` method, allowing users to retrieve their list of favorite recipes.

## Relationship with Models
The `FavoritesController` interacts primarily with the `Recipe` model through the following relationships:

- **User-Favorites Relationship**: The user has a many-to-many relationship with recipes via a pivot table. This relationship allows for easy access to all recipes marked as favorites by a user.
  
- **Recipe-Category Relationship**: Recipes are related to categories, allowing categorization of recipes and loading of category-related data, such as recipe counts.
  
- **Recipe-Ingredients Relationship**: Each recipe can have multiple ingredients, and the controller counts the total number of ingredients associated with each recipe for better user insights.

This controller plays a crucial role in the overall functionality of the NutriPlan application, making it a vital component that enhances user engagement and helps maintain organization in managing favorite recipes.