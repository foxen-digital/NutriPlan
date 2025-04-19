# Documentation: RecipeSearchController.php

Original file: `app/Http/Controllers/Api/RecipeSearchController.php`

# RecipeSearchController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: __invoke](#method-__invoke)
- [Routes](#routes)
- [Model: Recipe](#model-recipe)

## Introduction
The `RecipeSearchController` is part of the NutriPlan application, serving as an API endpoint for searching recipes based on user queries. This controller handles incoming requests to find and return recipes that match specific search criteria, ensuring only public recipes or those belonging to the authenticated user are included in the results. Its role is crucial in the recipe management and discovery functionalities within the NutriPlan application.

## Method: __invoke
The `__invoke` method within the `RecipeSearchController` handles the incoming requests for searching recipes. This method utilizes the Laravel framework's built-in capabilities to process, filter, and respond to user queries efficiently.

### Purpose
The primary purpose of this method is to perform a search operation based on a user-provided query and return the respective recipes as a JSON response.

### Parameters
- **Request $request**: The incoming request instance which contains the search query along with user authentication details.

### Return Values
- **JsonResponse**: Returns a JSON response containing an array of recipe data or an empty array if the search query is empty.

### Functionality
1. **Retrieve Query**:
   - The method retrieves the user's input query from the request or defaults it to an empty string if no query is provided.
   
   ```php
   $query = $request->input('query', '');
   ```

2. **Empty Query Check**:
   - If the query is empty, it sends back an empty data response:

   ```php
   if (empty($query)) {
       return response()->json([
           'data' => [],
       ]);
   }
   ```

3. **Recipe Query Construction**:
   - The method constructs a query to the `Recipe` model using two conditions:
     - It searches for recipes where the title or description contains the search query.
     - It ensures only public recipes or recipes owned by the authenticated user are included.
   
   ```php
   $recipes = Recipe::query()
       ->where(function (Builder $q) use ($query): void {
           $q->where('title', 'like', "%{$query}%")
             ->orWhere('description', 'like', "%{$query}%");
       })
       ->where(function (Builder $q) use ($request): void {
           /** @var \App\Models\User $user */
           $user = $request->user();
           $q->where('is_public', true)
             ->orWhere('user_id', $user->id);
       })
       ...
   ```

4. **Fetching and Returning Recipes**:
   - It selects specific recipe columns, limits the results to 10, and orders them by title. Finally, it returns the fetched recipes in a JSON response format.
   
   ```php
   return response()->json([
       'data' => $recipes,
   ]);
   ```

## Routes
The `RecipeSearchController` can be accessed via the following API route:

```php
Route::get('/api/recipes/search', RecipeSearchController::class);
```

This route maps a GET request to the `__invoke` method of `RecipeSearchController`, allowing users to perform a recipe search.

## Model: Recipe
The `Recipe` model represents the recipe entity within the NutriPlan application and includes essential attributes and relationships.

### Key Attributes
| Attribute    | Type             | Description                                                      |
|--------------|------------------|------------------------------------------------------------------|
| `id`         | Integer          | Primary key of the recipe record.                               |
| `title`      | String           | The title of the recipe.                                        |
| `description`| Text             | A detailed description of the recipe.                           |
| `slug`       | String           | A URL-friendly slug of the recipe title for routing purposes.   |
| `servings`   | Integer          | The number of servings the recipe yields.                       |
| `images`     | JSON             | An array of image URLs related to the recipe.                  |
| `user_id`    | Integer          | Foreign key linking to the user who created the recipe.         |
| `is_public`  | Boolean          | Indicates whether the recipe is publicly accessible.            |

### Relationships
- **User**: Each recipe belongs to a user, represented by the `user_id` attribute.

The `Recipe` model's relationships and behavior are crucial for ensuring that recipes can be correctly filtered and retrieved based on user permissions and visibility settings.