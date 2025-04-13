# Feature: Real-time Frontend Notifications for Recipe Import

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Features:**
- [Instruction Normalization](feature-instruction-normalization.md)
- [Asynchronous Recipe Import](feature-async-recipe-import.md)

## 1. Overview

This document details the implementation of real-time frontend notifications for the asynchronous recipe import process. Using Laravel Reverb and Echo, the user will be notified via a toast message when their queued recipe import completes, whether successfully or with an error.

## 2. Goals

-   Provide immediate, real-time feedback to the user on the outcome of their recipe import.
-   Utilize Laravel's broadcasting capabilities (Reverb, Echo).
-   Integrate seamlessly with the existing frontend notification/toast system.

## 3. Requirements

### 3.1. Backend (Reverb & Events)

-   Laravel Reverb must be installed, configured (`php artisan reverb:install`, `.env` variables), migrated (`php artisan migrate`), and running (`php artisan reverb:start`).
-   Broadcasting routes must be enabled in `routes/channels.php` for private channels (e.g., `Broadcast::channel('user.{id}', function ($user, $id) { return (int) $user->id === (int) $id; });`).
-   A new broadcast event `App\Events\RecipeImportCompleted` must be created.
    -   It must implement `Illuminate\Contracts\Broadcasting\ShouldBroadcast`.
    -   The constructor should accept:
        -   `int $userId`: The ID of the user to notify.
        -   `string $status`: 'success' | 'error'
        -   `string $message`: User-friendly status message.
        -   `?int $recipeId`: (Nullable) The ID of the successfully imported recipe.
    -   It must define a `broadcastOn()` method returning `new PrivateChannel('user.' . $this->userId)`.
    -   It should define a `broadcastAs()` method returning a clear event name (e.g., `'recipe.import.completed'`).
    -   It should define a `broadcastWith()` method returning an array with `status`, `message`, and `recipeId`.
-   The `App\Jobs\ImportRecipeJob` must be modified:
    -   The core import logic within the `handle` method should be wrapped in a `try...catch` block.
    -   On successful import: Dispatch `RecipeImportCompleted::dispatch($this->userId, 'success', 'Recipe imported successfully!', $recipe->id);` (adjust message as needed).
    -   On exception/failure: Dispatch `RecipeImportCompleted::dispatch($this->userId, 'error', 'Failed to import recipe. Please check the URL or try again later.', null);` (adjust message as needed).

### 3.2. Frontend (Echo & Notifications)

-   Laravel Echo must be installed and configured (`resources/js/bootstrap.js`) to use the appropriate broadcaster (Reverb).
-   The frontend JavaScript (e.g., in `resources/js/app.js` or a root layout component) must listen for the broadcast event on the authenticated user's private channel.
-   The listener callback must trigger the existing frontend notification/toast component.
-   The notification component should display the `message` received from the event.
-   The notification component should use the `status` ('success' or 'error') from the event to determine the style/type of the toast (e.g., green for success, red for error).
-   Optionally, if the status is 'success' and `recipeId` is present, the notification could include a link to the newly imported recipe page.

## 4. Technical Design

-   **Event Class (`RecipeImportCompleted`):** Standard Laravel event class implementing `ShouldBroadcast`. Ensure properties (`userId`, `status`, `message`, `recipeId`) are public or have corresponding getters for serialization in `broadcastWith()`.
-   **Job Integration:** Modify the `handle` method of `ImportRecipeJob` to include the `try...catch` block and dispatch the event in both `try` (after success) and `catch` blocks.
-   **Frontend Listener:**
    ```javascript
    // Assuming Echo is initialized in bootstrap.js
    // Assuming userId is available (e.g., passed via Blade or fetched)
    if (typeof userId !== 'undefined') { // Check if userId is available
        window.Echo.private(`user.${userId}`)
            .listen('.recipe.import.completed', (e) => {
                console.log('Recipe Import Event Received:', e); // For debugging
                // Assuming a global function showAppNotification exists
                showAppNotification({
                    type: e.status, // 'success' or 'error'
                    message: e.message,
                    // Optional: Add logic to generate link if e.recipeId exists
                    link: e.status === 'success' && e.recipeId ? `/recipes/${e.recipeId}` : null
                });
            });
    }
    ```
-   **Notification Component:** Requires an existing frontend component capable of displaying different types (success/error) of toast notifications and potentially handling links.

## 5. Implementation Plan

1.  Ensure Reverb is installed, configured, migrated, and running.
2.  Ensure `routes/channels.php` authorizes the private user channel.
3.  Create the `App\Events\RecipeImportCompleted` event class with the required properties and methods (`broadcastOn`, `broadcastAs`, `broadcastWith`).
4.  Modify `App\Jobs\ImportRecipeJob` to dispatch the event on success and failure.
5.  Ensure Echo is configured in `resources/js/bootstrap.js`.
6.  Implement the Echo listener in the appropriate frontend JavaScript file.
7.  Connect the Echo listener callback to the existing notification component, passing the status, message, and optional link.
8.  Test the end-to-end flow: trigger an import, verify the job runs, verify the event is broadcast (check Reverb debug logs or browser network tools), verify the frontend receives the event and displays the correct notification.
9.  Write feature tests simulating the event broadcast and potentially frontend tests (if using tools like Laravel Dusk) to verify the notification appears.

## 6. Future Considerations

-   Providing more detailed error messages from the job.
-   Adding progress updates for potentially very long imports (more complex, might involve multiple events).
-   Handling authorization failures in the channel definition more gracefully. 