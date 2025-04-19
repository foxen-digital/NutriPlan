# Documentation: ItemSearchController.php

Original file: `app/Http/Controllers/Api/ItemSearchController.php`

# ItemSearchController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Methods](#class-methods)
  - [__invoke](#invoke)
- [Routes](#routes)
- [Models](#models)

## Introduction
The `ItemSearchController` is a controller class in the NutriPlan application, specifically designed for handling API requests associated with item searches. It enables users to search for ingredients and shopping list items by name and returns a list of unique results based on the user's input. This controller plays a vital role in enhancing the user experience by providing efficient search capabilities for a cooking application.

## Class Methods

### __invoke
```php
public function __invoke(Request $request): JsonResponse
```

#### Purpose
The `__invoke` method processes incoming HTTP requests to perform a search for shopping list items and ingredients based on a user-provided query string.

#### Parameters
- `Request $request`: An instance of the `Illuminate\Http\Request` class that encapsulates the HTTP request, containing parameters like the search query.

#### Return Values
- Returns a `JsonResponse`: This response contains an array of unique search results, formatted as a JSON object.

#### Functionality
1. **Query Extraction:**
   - The method first extracts the search query from the request using `$request->input('query')`.

2. **Validation:**
   - It checks if the query is empty or shorter than two characters. If either condition is true, it returns an empty JSON array.

3. **Database Queries:**
   - Searches in `ShoppingListItem` names using a wildcard search (`LIKE`) for names that contain the query string.
   - Searches in `Ingredient` names using the same method.
   - Both searches limit the results to 5 distinct entries and extract just the names using the `pluck('name')` method.

4. **Result Combination:**
   - Combines the results from both searches, ensuring uniqueness by using the `unique` method, and resets the keys with `values()`.
   - Converts the combined collection to an array.

5. **Response Generation:**
   - Returns the resulting array as a JSON response through `response()->json($results)`.

## Routes
The `ItemSearchController` is typically associated with API routes configured in the routes file. It handles requests to endpoints like:
```php
Route::get('/api/item-search', ItemSearchController::class);
```
This route allows clients to perform a search operation by making a GET request to the `/api/item-search` endpoint, facilitating the search functionality provided by the controller.

## Models

### Ingredient
- **Relationships:** The `Ingredient` model likely has relationships set up with other models (e.g., recipes or meal plans) to represent its associations in the application.
- **Important Attributes:** 
  - `name`: The name of the ingredient, which is the primary field being searched in this controller.

### ShoppingListItem
- **Relationships:** The `ShoppingListItem` model may be associated with shopping lists or user accounts, representing the items a user intends to purchase.
- **Important Attributes:** 
  - `name`: The name of the shopping list item, which is also searched in this controller.

This documentation covers the essential functionality of the `ItemSearchController`, highlighting its purpose, method details, routing, and associated models to ensure that developers can understand and effectively utilize this part of the NutriPlan application.