# Documentation: ApiTokenController.php

Original file: `app/Http/Controllers/Settings/ApiTokenController.php`

# ApiTokenController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes Handled](#routes-handled)
- [Methods](#methods)
  - [index](#index)
  - [store](#store)
  - [destroy](#destroy)

## Introduction
The `ApiTokenController` class is responsible for managing API tokens within the application's settings. This controller provides functionality for users to view, create, and delete their personal API tokens. It leverages the Inertia.js framework to render the views, facilitating a smooth user experience when interacting with tokens in the web application.

## Routes Handled
The following routes are handled by this controller:

- `GET /settings/tokens` - Displays a list of the user's API tokens.
- `POST /settings/tokens` - Creates a new API token for the user.
- `DELETE /settings/tokens/{tokenId}` - Deletes a specified API token.

## Methods

### index
```php
public function index(Request $request): Response
```
#### Purpose
Displays a list of the user's API tokens.

#### Parameters
- `Request $request`: The HTTP request instance containing the user's data.

#### Return Value
- Returns an `Inertia\Response` that renders the `settings/ApiTokens` view along with the user's tokens.

#### Functionality
This method retrieves the currently authenticated user's API tokens by accessing `$request->user()->tokens`. It then utilizes the Inertia.js render method to return a response that includes the tokens for rendering in the frontend. This allows for a centralized management page where users can easily view all their tokens.

---

### store
```php
public function store(Request $request): RedirectResponse
```
#### Purpose
Stores a newly created API token in storage.

#### Parameters
- `Request $request`: The HTTP request instance containing the token information.

#### Return Value
- Returns a `RedirectResponse` that redirects the user to the tokens index page, along with a success message and the plain text token.

#### Functionality
1. **Validation**: The method begins by validating the request data to ensure that the `name` field is provided, is a string, and does not exceed 255 characters.
2. **Token Creation**: Upon successful validation, a new token is created using the user's `createToken` method with the validated name.
3. **Redirect**: Finally, the method redirects the user to the tokens index page and passes both the created token and a status message indicating that the token has been created.

---

### destroy
```php
public function destroy(Request $request, string $tokenId): RedirectResponse
```
#### Purpose
Removes the specified API token from storage.

#### Parameters
- `Request $request`: The HTTP request instance containing user data.
- `string $tokenId`: The identifier of the token to be deleted.

#### Return Value
- Returns a `RedirectResponse` that redirects the user back to the tokens index page with a status message confirming deletion.

#### Functionality
1. **Token Deletion**: The method retrieves the currently authenticated user and searches for the token with the given `tokenId`. If found, that token is deleted from the user's token collection.
2. **Redirect**: It then redirects the user to the tokens index page, providing a status message that indicates the token has been successfully deleted.

## Conclusion
The `ApiTokenController` provides crucial functionality for managing user-specific API tokens in the NutriPlan application. By using clear methods for listing, creating, and deleting tokens, it enhances the overall usability of the API token management system. This documentation aims to provide a detailed understanding of the controller's purpose and operations, ensuring that developers can efficiently interact with and utilize this class within the application.