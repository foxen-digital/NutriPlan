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
-   Provide secure API endpoints for users to create, list, and revoke their own API tokens.
-   Create a dedicated settings page in the user interface for token management.
-   Ensure tokens are displayed only once upon creation for security.

## 3. Requirements

### 3.1. Backend: Sanctum Setup & Configuration

-   **Install & Configure Sanctum:**
    -   Ensure Laravel Sanctum is installed (`composer require laravel/sanctum`).
    -   Publish Sanctum configuration and migration files (`php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`).
    -   Run migrations (`php artisan migrate`).
    -   Add `Laravel\Sanctum\HasApiTokens` trait to the `App\Models\User` model.

### 3.2. Backend: API Token Management Endpoints

-   **API Routes (`routes/api.php`):**
    -   Create routes within a `/v1` group, protected by the `auth:sanctum` middleware.
    -   `GET /api/v1/tokens`: List the authenticated user's tokens.
    -   `POST /api/v1/tokens`: Create a new token for the authenticated user. Requires a `name` parameter in the request body.
    -   `DELETE /api/v1/tokens/{tokenId}`: Delete a specific token belonging to the authenticated user.
-   **Controller (`App\Http\Controllers\Api\V1\ApiTokenController`):**
    -   `index()`: Retrieve and return a list of the authenticated user's tokens (e.g., ID, name, abilities, last used timestamp, created timestamp). **Do not include the token value.**
    -   `store(Request $request)`:
        -   Validate the request (require `name` string).
        -   Create a new token using `$request->user()->createToken($request->name)`.
        -   Return a JSON response containing the token's metadata *and* the `plainTextToken`. Status code 201 (Created).
    -   `destroy(Request $request, $tokenId)`:
        -   Find the token belonging to the authenticated user by its ID.
        -   If found, delete the token using `$request->user()->tokens()->where('id', $tokenId)->delete()`.
        -   Return an appropriate response (e.g., 204 No Content on success, 404 Not Found if token doesn't exist or belong to the user).

### 3.3. Frontend Settings Page

-   **Vue Component (`resources/js/pages/Settings/ApiTokens.vue`):**
    -   Fetch the user's tokens from the `GET /api/v1/tokens` endpoint on component mount.
    -   Display a list/table of existing tokens showing their name, creation date, and last used date (if available).
    -   Provide a button/form element to trigger the token revocation (calling `DELETE /api/v1/tokens/{tokenId}`). Confirm deletion with the user.
    -   Provide a form to create a new token:
        -   Input field for the token name.
        -   Button to submit the creation request to `POST /api/v1/tokens`.
    -   **Crucially:** When a new token is created, display the received `plainTextToken` clearly to the user *immediately* after creation. Include a strong warning that this is the only time the token will be shown and it must be copied securely.
    -   Handle loading states and potential API errors gracefully.
-   **Layout Update (`resources/js/layouts/settings/Layout.vue`):**
    -   Add a navigation link in the settings sidebar/menu pointing to the "API Tokens" page (`/settings/api-tokens`).

## 4. Technical Design Notes

-   **Security:** Emphasize that API tokens grant access to the user's account and should be protected like passwords. Sanctum handles secure hashing.
-   **Token Abilities (Scopes):** Initially, tokens will likely have default abilities. Future enhancements could allow creating tokens with specific limited permissions (e.g., only allow recipe import).
-   **Error Handling:** Implement robust error handling for API requests on the frontend.

## 5. Implementation Plan

1.  Install/Configure Sanctum, run migrations, update User model.
2.  Define API routes in `routes/api.php`.
3.  Implement `ApiTokenController` with `index`, `store`, `destroy` methods.
4.  Write feature tests for the `ApiTokenController` endpoints (authentication, creation, listing, deletion, ensuring token value is only shown on creation).
5.  Create `ApiTokens.vue` component.
6.  Implement the UI for listing, creating, and deleting tokens, including API calls.
7.  Implement the secure display of the newly generated token.
8.  Add the navigation link to the settings layout.
9.  Manually test the full flow of token management via the UI.

## 6. Future Considerations

-   Implementing token abilities/scopes for fine-grained permissions.
-   Adding options for token expiration dates.
-   UI improvements for token management (e.g., search/filter). 