# Documentation: web.php

Original file: `routes/web.php`

# web.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Route Definitions](#route-definitions)
  - [Home Route](#home-route)
  - [Dashboard Route](#dashboard-route)
  - [Authenticated Routes](#authenticated-routes)
    - [Recipes](#recipes)
    - [Categories](#categories)
    - [Ingredients](#ingredients)
    - [Collections](#collections)
    - [Favorites](#favorites)
    - [Meal Plans](#meal-plans)
    - [Shopping Lists](#shopping-lists)
  - [Unauthenticated Routes](#unauthenticated-routes)
  - [Demo Route](#demo-route)
  - [Debug Routes](#debug-routes)
- [Conclusion](#conclusion)

## Introduction
The `web.php` file is responsible for defining the HTTP routes for the NutriPlan application. It organizes the routes into groups based on user authentication requirements and categorizes them by the type of functionality, such as recipes, meal plans, and shopping lists. This file serves as the routing configuration for the application's web interface, utilizing Laravel's routing capabilities to clearly define how different requests are handled.

## Route Definitions

### Home Route
```php
Route::get('/', function () {
    return Inertia::render('Landing');
})->name('home');
```
- **Purpose**: Displays the landing page of the application.
- **Parameters**: None
- **Return Value**: Renders the `Landing` component using Inertia.js.

### Dashboard Route
```php
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth:sanctum', 'verified'])->name('dashboard');
```
- **Purpose**: Displays the user dashboard.
- **Parameters**: None
- **Return Value**: Renders the `Dashboard` component. It requires authentication and verification.

### Authenticated Routes
Routes that are only accessible to authenticated users, protected by the `auth:sanctum` middleware.

#### Recipes
```php
Route::resource('recipes', RecipeController::class);
Route::post('recipes/import', RecipeImportController::class)->name('recipes.import');
Route::get('recipes/by/{user}', [UserRecipeController::class, 'index'])->name('recipes.by-user');
```
- **Purpose**: Handle all CRUD operations related to recipes.
- **Controllers**: 
   - `RecipeController`: Standard resource operations.
   - `RecipeImportController`: Handles recipe import functionality.
   - `UserRecipeController`: Retrieves recipes owned by a specific user.
- **Parameters**: 
   - `{user}` in the `recipes/by/{user}` route refers to the User model.

#### Categories
```php
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::post('/categories', [CategoryController::class, 'store']);
```
- **Purpose**: Manage categories associated with recipes.
- **Controllers**:
   - `CategoryController`: Handles fetching and storing categories.
- **Parameters**:
   - `{category:slug}` retrieves a specific category based on its slug.

#### Ingredients
```php
Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
```
- **Purpose**: Store ingredients.
- **Controller**: 
   - `IngredientController`: Handles storing ingredient information.

#### Collections
```php
Route::resource('collections', CollectionController::class);
Route::post('collections/add-recipe', [CollectionRecipeController::class, 'store'])->name('collections.add-recipe');
Route::delete('collections/{collection}/recipes/{recipe}', [CollectionRecipeController::class, 'destroy'])->name('collections.remove-recipe');
```
- **Purpose**: Manage recipe collections.
- **Controllers**:
   - `CollectionController`: Standard resource operations for collecting recipes.
   - `CollectionRecipeController`: Manages adding and removing recipes from collections.

#### Favorites
```php
Route::post('recipes/{recipe}/favorite', FavoriteController::class)->name('recipes.favorite');
Route::get('favorites', [FavoritesController::class, 'index'])->name('favorites.index');
```
- **Purpose**: Handle favoriting recipes.
- **Controllers**:
   - `FavoriteController`: Allows users to favorite a recipe.
   - `FavoritesController`: Retrieves a list of favorit recipes.

#### Meal Plans
```php
Route::resource('meal-plans', MealPlanController::class)->except(['edit', 'update'])->parameters(['meal-plans' => 'mealPlan']);
Route::post('meal-plans/add-recipe', [MealPlanRecipeController::class, 'store'])->name('meal-plans.add-recipe');
Route::post('meal-plans/{mealPlan}/copy', MealPlanCopyController::class)->name('meal-plans.copy');
Route::post('meal-plans/{mealPlan}/shopping-list', ShoppingListGenerationController::class)->name('meal-plans.generate-shopping-list');
Route::delete('meal-plans/{mealPlan}/recipes/{recipe}', [MealPlanRecipeController::class, 'destroy'])->name('meal-plans.remove-recipe');
```
- **Purpose**: Manage meal plans, including adding recipes and generating shopping lists.
- **Controllers**:
   - `MealPlanController`: Handles basic meal plan operations.
   - `MealPlanRecipeController`: Manages the addition and removal of recipes to/from meal plans.
   - `MealPlanCopyController`: Facilitates copying existing meal plans.
   - `ShoppingListGenerationController`: Generates shopping lists from meal plans.

#### Shopping Lists
```php
Route::resource('shopping-lists', ShoppingListController::class);
Route::post('shopping-lists/{shoppingList}/items', [ShoppingListItemController::class, 'store'])->name('shopping-lists.items.store');
Route::put('shopping-lists/{shoppingList}/items/{item}', [ShoppingListItemController::class, 'update'])->name('shopping-lists.items.update');
Route::delete('shopping-lists/{shoppingList}/items/{item}', [ShoppingListItemController::class, 'destroy'])->name('shopping-lists.items.destroy');
Route::post('shopping-lists/{shoppingList}/items/{item}/toggle-purchased', ShoppingListItemPurchaseController::class)->name('shopping-lists.items.toggle-purchased');
Route::put('shopping-lists/{shoppingList}/order-items', ShoppingListItemOrderController::class)->name('shopping-lists.items.order');
```
- **Purpose**: Manage shopping lists, including the items within them.
- **Controllers**:
   - `ShoppingListController`: Manages shopping lists.
   - `ShoppingListItemController`: Handles items within a shopping list.
   - `ShoppingListItemPurchaseController`: Manages the state of an item (purchased/not purchased).
   - `ShoppingListItemOrderController`: Manages the order of items within a shopping list.

### Unauthenticated Routes
These routes are for unauthenticated users and are not listed in this file, focusing instead on authenticated