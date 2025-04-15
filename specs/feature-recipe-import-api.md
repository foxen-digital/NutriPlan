# Feature: Recipe Import API Endpoint

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Features:**
- [Sanctum API Token Management](feature-sanctum-api-tokens.md)
- [Browser Extensions Core](feature-browser-extensions-core.md)
- [Asynchronous Recipe Import](feature-async-recipe-import.md)

## 1. Overview

This specification defines the dedicated API endpoint that external applications (like the browser extension) will use to submit URLs for recipe importing. This endpoint leverages Laravel Sanctum for authentication and utilizes the existing asynchronous recipe import job.

## 2. Goals

-   Provide a secure, authenticated API endpoint for triggering recipe imports.
-   Accept a URL via a POST request.
-   Integrate with the existing `ImportRecipeJob`.
-   Provide a clear JSON response indicating the job has been queued.

## 3. Requirements

### 3.1. Backend: Recipe Import API Endpoint

-   **API Route (`routes/api.php`):**
    -   Create a new API route protected by `auth:sanctum`:
        -   `POST /api/recipes/import-via-extension`
-   **Controller (e.g., `App\Http\Controllers\Api\RecipeImportController`):**
    -   Create a new controller or add a method to an existing relevant API controller.
    -   `importViaExtension(Request $request)` method:
        -   **Authentication:** The `auth:sanctum` middleware handles authentication via the Bearer token. Access the authenticated user via `$request->user()`.
        -   **Validation:** Validate the incoming JSON payload:
            -   `url`: Required, must be a valid URL string.
        -   **Job Dispatch:** If validation passes, dispatch the existing `App\Jobs\ImportRecipeJob`:
            ```php
            ImportRecipeJob::dispatch(
                $request->input('url'),
                $request->user()->id
            );
            ```
        -   **Response:** Return a JSON response indicating success:
            ```json
            {
              "message": "Recipe import queued successfully."
            }
            ```
            Use HTTP status code 202 (Accepted).
        -   **Error Handling:** Laravel's default validation exception handling should return a 422 response with errors if validation fails. Handle other potential exceptions (e.g., issues dispatching the job) appropriately, returning a 500 status code.

## 4. Technical Design Notes

-   **Endpoint Naming:** The name `import-via-extension` makes its intended initial use clear, but it's generic enough for other potential API clients.
-   **Simplicity:** The endpoint's sole responsibility is validation and job dispatching, keeping it lean. The actual import logic remains within the `ImportRecipeJob`.

## 5. Implementation Plan

1.  Define the `POST /api/recipes/import-via-extension` route in `routes/api.php`, applying the `auth:sanctum` middleware.
2.  Create the `RecipeImportController` (or add the method to an existing controller).
3.  Implement the `importViaExtension` method, including validation and dispatching `ImportRecipeJob`.
4.  Write feature tests for this endpoint:
    -   Test successful queuing with a valid URL and token.
    -   Test authentication failure (missing/invalid token).
    -   Test validation failure (missing/invalid URL).
    -   Verify that the `ImportRecipeJob` is actually dispatched with the correct parameters.

## 6. Future Considerations

-   Adding rate limiting to the API endpoint.
-   Allowing additional parameters (e.g., specifying a collection to import into). 