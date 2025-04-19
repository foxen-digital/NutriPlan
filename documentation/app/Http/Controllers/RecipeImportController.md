# Documentation: RecipeImportController.php

Original file: `app/Http/Controllers/RecipeImportController.php`

# RecipeImportController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: __invoke](#method-__invoke)
- [Routes](#routes)

## Introduction

The `RecipeImportController` is a pivotal component of the NutriPlan application that orchestrates the importing of recipes from external sources. When users wish to include a recipe from a specific URL, this controller manages the process of queuing an import job that operates asynchronously. By leveraging the job queue system, the application can handle potentially long-running tasks, like fetching and processing a recipe, without blocking the user interface. Upon triggering the import job, users are notified immediately that their request has been queued.

## Method: __invoke

### Purpose
The `__invoke` method in this controller serves as a single-entry point for handling recipe import requests. It facilitates the dispatching of a job that is responsible for importing a recipe from a specified URL.

### Parameters
| Parameter                     | Type                   | Description                                           |
|-------------------------------|------------------------|-------------------------------------------------------|
| `$request`                    | `ImportRecipeRequest`   | An instance of the validated request containing the recipe URL.  |

### Return Values
- `RedirectResponse`: This method returns a redirect response to the previous page with a success message indicating that the recipe import has been queued.

### Functionality
1. **Job Dispatching**: 
   - The method retrieves the URL of the recipe from the request object using `$request->input('url')`.
   - It also gets the authenticated user's ID using `auth()->id()`.
   - An instance of the `ImportRecipeJob` is dispatched with the provided URL and user ID, which places the import task in the job queue for processing.

2. **Immediate User Feedback**: 
   - After dispatching the job, the method calls `back()->with()` to redirect the user back to the previous page. It attaches a success message to the session, which can be displayed to the user, informing them that the recipe import is in progress.

Here is the method code highlight for easier understanding:

```php
public function __invoke(ImportRecipeRequest $request): RedirectResponse
{
    // Dispatch the job to import the recipe asynchronously
    ImportRecipeJob::dispatch(
        $request->input('url'),
        auth()->id()
    );

    // Return an immediate response to the user
    return back()->with('success', 'Recipe queued for import. You will be notified when it completes.');
}
```

## Routes

The `RecipeImportController` is typically associated with a route that allows users to submit a recipe URL for import. Below is an example of the route that might be defined in the application routes file:

```php
use App\Http\Controllers\RecipeImportController;

Route::post('/import-recipe', RecipeImportController::class);
```

This route listens for POST requests at the `/import-recipe` endpoint and invokes the `__invoke` method of the `RecipeImportController`. 

---

This documentation provides a detailed understanding of the `RecipeImportController`, its methods, and its purpose within the NutriPlan application. By following the outlined structure, developers should be able to comprehend how to integrate and utilize this controller effectively in their application workflows.