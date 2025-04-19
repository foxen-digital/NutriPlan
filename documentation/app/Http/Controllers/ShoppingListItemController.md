# Documentation: ShoppingListItemController.php

Original file: `app/Http/Controllers/ShoppingListItemController.php`

# ShoppingListItemController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [store](#store)
  - [update](#update)
  - [destroy](#destroy)
- [Handled Routes](#handled-routes)
- [Models](#models)

## Introduction
The `ShoppingListItemController` is part of the NutriPlan application, specifically designed to manage custom items within a shopping list. This controller takes care of adding, updating, and removing items from a shopping list, ensuring that actions are authorized and related to the correct shopping list. It utilizes Laravel's request validation and authorization features to ensure data integrity and security.

## Methods

### store
```php
public function store(StoreShoppingListItemRequest $request, ShoppingList $shoppingList): RedirectResponse
```
- **Purpose:** Handles the addition of a new custom item to an existing shopping list.
- **Parameters:**
  - `StoreShoppingListItemRequest $request`: An instance of the request containing validated data for the shopping list item.
  - `ShoppingList $shoppingList`: The shopping list to which the item will be added.
- **Return Value:** RedirectResponse indicating the result of the operation.
- **Functionality:**
  - Authorizes the user to update the specified `shoppingList`.
  - Creates a new `ShoppingListItem` associated with the provided `shoppingList`, marking it as custom (`is_custom` set to `true`).
  - Redirects to the `shopping-lists.show` route for the corresponding `shoppingList`, carrying a success message.

### update
```php
public function update(UpdateShoppingListItemRequest $request, ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
```
- **Purpose:** Updates an existing custom shopping list item.
- **Parameters:**
  - `UpdateShoppingListItemRequest $request`: An instance containing validated data for updating the shopping list item.
  - `ShoppingList $shoppingList`: The shopping list that contains the item.
  - `ShoppingListItem $item`: The item that needs to be updated.
- **Return Value:** RedirectResponse indicating the result of the update operation.
- **Functionality:**
  - Authorizes the user to update the specified `shoppingList`.
  - Checks whether the item belongs to the provided shopping list; if not, it aborts with a 404 error.
  - Updates the item with validated data from the request.
  - Redirects to the `shopping-lists.show` route for the `shoppingList`, including a success message.

### destroy
```php
public function destroy(ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
```
- **Purpose:** Removes a custom item from a shopping list.
- **Parameters:**
  - `ShoppingList $shoppingList`: The shopping list from which the item will be removed.
  - `ShoppingListItem $item`: The item to be deleted.
- **Return Value:** RedirectResponse indicating the result of the delete operation.
- **Functionality:**
  - Authorizes the user to update the specified `shoppingList`.
  - Confirms the item belongs to the specified shopping list, aborting with a 404 error if it does not.
  - Deletes the specified item from the database.
  - Redirects to the `shopping-lists.show` route for the `shoppingList`, including a success message.

## Handled Routes
The `ShoppingListItemController` corresponds to the following routes in the application:
- **POST** `/shopping-lists/{shoppingList}/items`: Calls the `store` method to add a new item.
- **PUT** `/shopping-lists/{shoppingList}/items/{item}`: Calls the `update` method to modify an existing item.
- **DELETE** `/shopping-lists/{shoppingList}/items/{item}`: Calls the `destroy` method to remove an item.

## Models
The controller interacts with the following models:

### ShoppingList
- **Relationships:** 
  - Has many `ShoppingListItem`.
- **Important Attributes:**
  - `id`: Unique identifier for the shopping list.
  - `user_id`: The owner of the shopping list.

### ShoppingListItem
- **Relationships:** 
  - Belongs to a `ShoppingList`.
- **Important Attributes:**
  - `id`: Unique identifier for the shopping list item.
  - `shopping_list_id`: Foreign key linking to the `ShoppingList` it belongs to.
  - `is_custom`: Boolean indicating whether the item is custom.

This documentation should serve as a comprehensive guide to understanding and working with the `ShoppingListItemController` class in the NutriPlan application. Whether you're looking to maintain or extend functionality, this guide provides adequate insight into its structure and purpose.