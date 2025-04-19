# Documentation: api.php

Original file: `routes/api.php`

# api.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes Overview](#routes-overview)
  - [User Route](#user-route)
  - [Recipe Search Route](#recipe-search-route)
  - [Barcode Lookup Route](#barcode-lookup-route)
  - [Item Search Route](#item-search-route)
  - [Recipe Import Route](#recipe-import-route)

## Introduction
The `api.php` file is a key component of the NutriPlan application that defines the API routes used for various functionalities related to recipes, barcode lookups, and item searches. This file uses Laravel's routing system to set up API endpoints and integrates middleware for authentication and security. The routes defined in this file help facilitate interactions between the client application and the server, ensuring that requests are handled appropriately based on user authentication status.

## Routes Overview
The routes defined in this file can be categorized based on their authentication requirements and related functionality.

### User Route
```php
Route::middleware('auth')->get('/user', function (Request $request) {
    return $request->user();
});
```
- **Purpose**: This route is used to retrieve the authenticated user's information.
- **Parameters**: 
  - `Request $request`: The incoming request instance.
- **Return Value**: Returns the authenticated user's information in JSON format.
- **Middleware**: Requires the user to be authenticated.

### Recipe Search Route
```php
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('recipes/search', RecipeSearchController::class)->name('api.recipes.search');
});
```
- **Purpose**: This route allows users to search for recipes based on specific query parameters.
- **Controller Handling**: `RecipeSearchController` is responsible for processing the search and returning results.
- **Middleware**: Requires users to be authenticated via the Sanctum method, allowing for session-based authentication.

### Barcode Lookup Route
```php
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('barcode-lookup', [BarcodeLookupController::class, 'lookup'])->name('api.barcode-lookup');
});
```
- **Purpose**: This route allows users to look up information based on a barcode.
- **Parameters**: 
  - Requires POST data containing the barcode.
- **Return Value**: Returns information related to the provided barcode.
- **Controller Handling**: `BarcodeLookupController` processes the lookup.
- **Middleware**: Requires users to be authenticated via the Sanctum method.

### Item Search Route
```php
Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('item-search', ItemSearchController::class)->name('api.item-search');
});
```
- **Purpose**: This route facilitates searching for items within the system.
- **Controller Handling**: `ItemSearchController` manages the search logic and output.
- **Middleware**: Requires users to be authenticated via the Sanctum method.

### Recipe Import Route
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('recipes/import-via-extension', RecipeImportController::class)
        ->name('api.recipes.import-via-extension');
});
```
- **Purpose**: This route is used for importing recipes from external extensions.
- **Parameters**: 
  - Requires POST body containing recipe data or extension details.
- **Return Value**: Returns confirmation of the import operation and possibly the imported recipe details.
- **Controller Handling**: Managed by `RecipeImportController`.
- **Middleware**: Access is restricted to authenticated users via Sanctum.

## Conclusion
The `api.php` file serves as the backbone for handling important API interactions in the NutriPlan application. By defining a clear structure for routes and utilizing Laravel's middleware for managing authentication, it ensures secure and efficient communication between client requests and server responses. Developers can extend or modify the current routes while adhering to the established structure for consistency and maintainability.