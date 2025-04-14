# Feature: Asynchronous Recipe Import

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Features:**
- [Instruction Normalization](feature-instruction-normalization.md)
- [Instruction Normalization Service](feature-instruction-normalization-service.md)
- [Recipe Parser Integration](feature-instruction-parser-integration.md)
- [Real-time Frontend Notifications](feature-real-time-notifications.md)

## 1. Overview

This document outlines the plan to refactor the recipe import process to run asynchronously using a queued job. This is necessary because the integration of LLM-based normalization for both ingredients and instructions significantly increases the processing time for imports, which would block the UI if run synchronously.

## 2. Goals

-   Prevent UI blocking during recipe import.
-   Improve user experience by providing immediate feedback that the import has started.
-   Structure the import logic within a standard Laravel queued job.
-   Prepare the backend for real-time status updates.

## 3. Requirements

-   A new queued job `App\Jobs\ImportRecipeJob` must be created (`implements ShouldQueue`).
-   The job's constructor must accept:
    -   `string $url`: The URL of the recipe to import.
    -   `int $userId`: The ID of the user initiating the import.
-   The `handle` method of the job must contain the core logic for:
    -   Fetching the content from the provided `$url`.
    -   Invoking the `RecipeParser` (or a dedicated import action/service) to parse the content and create/update the `Recipe` model. This includes ingredient and instruction normalization.
    -   Ensuring the `userId` is correctly associated with the created/updated recipe (handling the removal of `Auth::id()` from the synchronous flow).
-   The controller/HTTP endpoint currently responsible for handling recipe imports must be modified:
    -   It should no longer perform the import synchronously.
    -   It should dispatch the `ImportRecipeJob` with the `$url` and the authenticated user's ID (`auth()->id()`).
    -   It should return an immediate response to the user indicating the import has been queued (e.g., a JSON response as defined in our notification-toasts.md spec).
-   The job must handle potential exceptions during the import process (e.g., network errors fetching URL, parsing errors, database errors) and log them appropriately.

## 4. Technical Design

-   **Job Class (`ImportRecipeJob`):**
    -   Use standard Laravel job structure (`Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`).
    -   Implement the `handle` method.
    -   Consider injecting necessary services (like a potential `RecipeImportService` or directly `RecipeParser`, HTTP client) into the `handle` method or constructor.
-   **Refactoring `RecipeParser`:**
    -   The dependency on `Auth::id()` needs removal. A `userId` parameter should be added to the `parse` method or, preferably, the `RecipeParser::fromItems` static method or the constructor, which the job will provide.
    -   Alternatively, create a dedicated `RecipeImportService` that orchestrates fetching the URL, calling `RecipeParser::fromItems` (passing the `userId`), and potentially handling initial setup or context.
The `ImportRecipeJob` would then call this service.
-   **Controller Modification:**
    -   Identify the controller method handling the `POST` request for recipe imports.
    -   Remove the synchronous import logic.
    -   Add `ImportRecipeJob::dispatch($request->input('url'), auth()->id());`.
    -   Return a response, e.g., `response()->json([
    'notification' => [
        'type' => 'success',
        'message' => 'Operation completed successfully',
        'duration' => 5000
    ]
], 202);`
-   **Error Handling:** Use `try...catch` blocks within the job's `handle` method to catch exceptions. Log errors using `Log::error()`. Integration with the notification system for failures will be handled in the *Real-time Frontend Notifications* feature.

## 5. Implementation Plan

1.  Create the `App\Jobs\ImportRecipeJob` class file.
2.  Define the constructor to accept `url` and `userId`.
3.  Refactor `RecipeParser` (or create `RecipeImportService`) to accept `userId` instead of using `Auth::id()`. Move fetching/parsing logic into the job's `handle` method or the new service.
4.  Implement the `handle` method in `ImportRecipeJob` to orchestrate the import.
5.  Add basic `try...catch` error logging in the `handle` method.
6.  Modify the import controller method to dispatch the job and return an immediate response.
7.  Configure queue worker (if not already done) and test the asynchronous import flow.
8.  Write feature tests for dispatching the job and potentially unit/integration tests for the job's `handle` logic (using mocking where appropriate).

## 6. Future Considerations

-   Adding job middleware for rate limiting or other concerns.
-   Implementing job batching if multiple imports can be triggered simultaneously.
-   Configuring job timeouts and retries specific to this import task. 