# Feature: Sanctum API Token Management

**Version:** 1.0
**Status:** Planned
**Date:** {{ CURRENT_DATE }}

**Related Features:**
- [Recipe Import API Endpoint](feature-recipe-import-api.md)
- [Browser Extensions Core](feature-browser-extensions-core.md)

## 1. Overview

This specification details the setup of Laravel Sanctum for API authentication and the creation of backend API endpoints and a frontend settings page for users to manage their Personal Access Tokens (PATs). These tokens will be used by third-party applications, such as the browser extension, to interact with the user's account securely.

## 2. Goals

-   Install and configure Laravel Sanctum for API authentication.
-   Provide secure web routes and controller actions for users to create, list, and revoke their own API tokens within the settings area.
-   Create a dedicated settings page in the user interface for token management.
-   Ensure tokens are displayed only once upon creation for security.

## 3. Requirements

### 3.1. Backend: Sanctum Setup & Configuration

-   **Install & Configure Sanctum:**
    -   Ensure Laravel Sanctum is installed (`composer require laravel/sanctum`).
    -   Publish Sanctum configuration and migration files (`php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`).
    -   Run migrations (`php artisan migrate`).
    -   Add `Laravel\Sanctum\HasApiTokens` trait to the `App\Models\User` model.

### 3.2. Backend: Token Management Routes & Controller

-   **Web Routes (`routes/settings.php`):**
    -   Define routes within the standard `web` middleware group, likely protected by the `auth` middleware.
    -   `GET /settings/tokens`: Display the token management page (handled by `index` method).
    -   `POST /settings/tokens`: Create a new token for the authenticated user (handled by `store` method). Requires a `name` parameter in the request body.
    -   `DELETE /settings/tokens/{tokenId}`: Delete a specific token belonging to the authenticated user (handled by `destroy` method).
-   **Controller (`App\Http\Controllers\Settings\ApiTokenController`):**
    -   `index()`: Retrieve the authenticated user's tokens (excluding the token value) and return the Inertia view (`Settings/ApiTokens`) passing the tokens as props.
    -   `store(Request $request)`:
        -   Validate the request (require `name` string).
        -   Create a new token using `$request->user()->createToken($request->name)`.
        -   Redirect back to the token settings page, flashing the token's metadata *and* the `plainTextToken` to the session for display by Inertia.
    -   `destroy(Request $request, $tokenId)`:
        -   Find the token belonging to the authenticated user by its ID.
        -   If found, delete the token using `$request->user()->tokens()->where('id', $tokenId)->delete()`.
        -   Redirect back to the token settings page, possibly with a success message. Handle cases where the token is not found.

### 3.3. Frontend Settings Page

-   **Vue Component (`resources/js/pages/Settings/ApiTokens.vue`):**
    -   Receive the user's tokens as props from the controller via Inertia (`index` method).
    -   Display a list/table of existing tokens showing their name, creation date, and last used date (if available).
    -   Provide a button/form element to trigger token revocation (using Inertia's method, e.g., `this.$inertia.delete('/settings/tokens/{tokenId}')`). Confirm deletion with the user.
    -   Provide a form to create a new token:
        -   Input field for the token name.
        -   Button to submit the creation request (using Inertia's method, e.g., `this.$inertia.post('/settings/tokens')`).
    -   **Crucially:** When a new token is created, the page will reload via the redirect. Use Inertia's flashed data mechanism to retrieve the `plainTextToken` from the session and display it clearly to the user *immediately* after creation. Include a strong warning that this is the only time the token will be shown and it must be copied securely.
    -   Handle loading states (using Inertia's progress indicators) and potential validation or server errors gracefully (using Inertia's error handling).
-   **Layout Update (`resources/js/layouts/settings/Layout.vue`):**
    -   Add a navigation link in the settings sidebar/menu pointing to the "API Tokens" page (`/settings/api-tokens`).

## 4. Technical Design Notes

-   **Security:** Emphasize that API tokens grant access to the user's account and should be protected like passwords. Sanctum handles secure hashing.
-   **Token Abilities (Scopes):** Initially, tokens will likely have default abilities. Future enhancements could allow creating tokens with specific limited permissions (e.g., only allow recipe import).
-   **Error Handling:** Implement robust error handling for API requests on the frontend.

## 5. Implementation Plan

1.  Install/Configure Sanctum, run migrations, update User model.
2.  Define web routes in `routes/settings.php`.
3.  Implement `App\Http\Controllers\Settings\ApiTokenController` with `index`, `store`, `destroy` methods tailored for Inertia responses (views, redirects with flashed data).
4.  Write feature tests for the `ApiTokenController` actions (authentication, creation, listing, deletion, checking flashed token value on creation).
5.  Create `ApiTokens.vue` component.
6.  Implement the UI for listing, creating, and deleting tokens using Inertia methods.
7.  Implement the display of the newly generated token using Inertia's flashed data.
8.  Add the navigation link to the settings layout.
9.  Manually test the full flow of token management via the UI.

## 6. Future Considerations

-   Implementing token abilities/scopes for fine-grained permissions.
-   Adding options for token expiration dates.
-   UI improvements for token management (e.g., search/filter). 