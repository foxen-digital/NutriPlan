# Documentation: RecipeController.php

Original file: `app/Http/Controllers/RecipeController.php`

# RecipeController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes](#routes)
- [Methods](#methods)
    - [index](#index)
    - [create](#create)
    - [store](#store)
    - [show](#show)
    - [edit](#edit)
    - [update](#update)
    - [destroy](#destroy)

## Introduction
The `RecipeController` class is responsible for handling all HTTP requests related to recipes in the NutriPlan application. It provides functionalities for creating, reading, updating, and deleting recipes. Utilizing Laravel's Inertia.js for rendering views, this controller also manages user interactions, filters recipes based on various criteria, and incorporates authorization checks to ensure that users have the appropriate permissions for each action.

## Routes
The following HTTP routes are associated with the `RecipeController`:

- `GET /recipes` - Displays a paginated list of recipes.
- `GET /recipes/create` - Returns a view for creating a new recipe.
- `POST /recipes` - Stores a new recipe in the database.
- `GET /recipes/{recipe}` - Displays the details of a specific recipe.
- `GET /recipes/{recipe}/edit` - Returns a view for editing an existing recipe.
- `PUT/PATCH /recipes/{recipe}` - Updates an existing recipe.
- `DELETE /recipes/{recipe}` - Deletes a specified recipe.

## Methods

### index
```php
public function index(Request $request): Response
```
#### Purpose
The `index` method retrieves a paginated list of recipes, applying any filters based on the user's input, and passes this data to a view.

#### Parameters
- `Request $request`: The incoming HTTP request containing parameters for filtering recipes.

#### Return Values
- Returns an `Inertia\Response` that renders the `Recipes/Index` component with recipes and filters.

#### Functionality
- Fetches the authenticated user.
- Builds a query for `Recipe` with eager loading of user and categories.
- Applies filters based on `category` and `show_mine` request parameters.
- Paginate results, limiting to 12 recipes per page.
- Adds an `is_favorited` flag to each recipe for user-specific data.
- Renders the component with all necessary information.

### create
```php
public function create(): Response
```
#### Purpose
This method returns a view for creating a new recipe, including category and ingredient options.

#### Return Values
- Returns an `Inertia\Response` for the `Recipes/Create` component.

#### Functionality
- Loads categories and ingredients from their respective models, ordering them by name.
- Passes necessary data to the front-end for rendering the recipe creation view, including measurement units from configuration.

### store
```php
public function store(CreateRecipeRequest $request): RedirectResponse
```
#### Purpose
The `store` method handles the creation of a new recipe based on validated input provided by the user.

#### Parameters
- `CreateRecipeRequest $request`: Contains validated data for storing a recipe.

#### Return Values
- Returns a `RedirectResponse` to the show page of the newly created recipe with a success message.

#### Functionality
- Creates a new recipe associated with the authenticated user using validated data.
- Handles the synchronization of category and ingredient relationships, mapping inputs appropriately.
- Redirects the user to the recipe detail page.

### show
```php
public function show(Recipe $recipe): Response
```
#### Purpose
Displays the details of a specific recipe, including its user, categories, nutrition information, and ingredients.

#### Parameters
- `Recipe $recipe`: An instance of the `Recipe` model.

#### Return Values
- Returns an `Inertia\Response` rendering the `Recipes/Show` component.

#### Functionality
- Authorizes the user to view the recipe.
- Eager loads associated data for the recipe.
- Checks if the recipe is favorited by the user and determines visibility for imported recipes based on ownership and public status.
- Passes required data for rendering the recipe details.

### edit
```php
public function edit(Recipe $recipe): Response
```
#### Purpose
Returns a view for editing an existing recipe.

#### Parameters
- `Recipe $recipe`: An instance of the `Recipe` model.

#### Return Values
- Returns an `Inertia\Response` for the `Recipes/Edit` component.

#### Functionality
- Authorizes the user for updating the recipe.
- Loads the recipe with related categories and ingredients.
- Prepares the necessary data for rendering the editing view.

### update
```php
public function update(UpdateRecipeRequest $request, Recipe $recipe): RedirectResponse
```
#### Purpose
Handles the updating of an existing recipe based on user input.

#### Parameters
- `UpdateRecipeRequest $request`: Contains validated data for updating the recipe.
- `Recipe $recipe`: The recipe instance to be updated.

#### Return Values
- Returns a `RedirectResponse` to the updated recipe's show page with a success message.

#### Functionality
- Authorizes the user for updating the recipe.
- Updates the recipe with validated data from the request.
- Synchronizes category and ingredient relationships if provided.
- Redirects to the recipe detail page.

### destroy
```php
public function destroy(Recipe $recipe, DeleteRecipeAction $deleteRecipeAction): RedirectResponse
```
#### Purpose
Handles the deletion of a specific recipe.

#### Parameters
- `Recipe $recipe`: The recipe instance to be deleted.
- `DeleteRecipeAction $deleteRecipeAction`: Service to handle the deletion process.

#### Return Values
- Returns a `RedirectResponse` to the index page with a success message.

#### Functionality
- Authorizes the user for deletion.
- Executes the deletion through the provided action.
- Redirects the user back to the recipe index page.

## Conclusion
The `RecipeController` class plays a crucial role in managing user interactions with recipes in the NutriPlan application. Its methods cover a range of functionalities from displaying to manipulating recipe data, ensuring that all operations are performed securely and efficiently. With the use of Inertia.js, the application enhances user experience by providing a seamless interface for managing recipes.