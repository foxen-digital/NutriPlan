# Documentation: ShoppingListController.php

Original file: `app/Http/Controllers/ShoppingListController.php`

# ShoppingListController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Routes Handled](#routes-handled)
- [Methods](#methods)
  - [index](#index)
  - [show](#show)
  - [store](#store)
  - [update](#update)
  - [destroy](#destroy)

## Introduction

The `ShoppingListController` class is responsible for managing shopping lists within the NutriPlan application. It provides methods for the following operations:
1. Fetching a list of shopping lists for the authenticated user.
2. Viewing a specific shopping list.
3. Creating a new shopping list.
4. Updating an existing shopping list.
5. Deleting a shopping list.

The controller utilizes Laravel's Inertia framework to render views and provides a strong emphasis on authorization to ensure that users can only perform operations on their own shopping lists.

## Routes Handled

The `ShoppingListController` handles the following routes:

| HTTP Method | Route                           | Action                       |
|-------------|---------------------------------|------------------------------|
| GET         | /shopping-lists                 | `index`                     |
| GET         | /shopping-lists/{shoppingList}  | `show`                      |
| POST        | /shopping-lists                 | `store`                     |
| PUT         | /shopping-lists/{shoppingList}  | `update`                    |
| DELETE      | /shopping-lists/{shoppingList}  | `destroy`                   |

## Methods

### index

```php
public function index(Request $request): Response
```

**Purpose:**  
Displays a listing of the authenticated user's shopping lists.

**Parameters:**
- `Request $request`: The incoming HTTP request.

**Return Value:**
- `Response`: An Inertia response rendering the `ShoppingLists/Index` view.

**Functionality:**  
This method retrieves all shopping lists associated with the authenticated user and includes a count of items for each list. It uses the Inertia library to render the index view for shopping lists, passing the retrieved shopping lists formatted as a resource collection.

### show

```php
public function show(ShoppingList $shoppingList): Response
```

**Purpose:**  
Displays the details of a specific shopping list.

**Parameters:**
- `ShoppingList $shoppingList`: The shopping list to be displayed.

**Return Value:**
- `Response`: An Inertia response rendering the `ShoppingLists/Show` view.

**Functionality:**  
The method first authorizes the action to ensure that the authenticated user has permission to view the specified shopping list. Upon successful authorization, it utilizes a shopping list service to prepare the shopping list for display and returns the corresponding view with the shopping list data.

### store

```php
public function store(StoreShoppingListRequest $request): RedirectResponse
```

**Purpose:**  
Creates a new empty shopping list.

**Parameters:**
- `StoreShoppingListRequest $request`: Validated request containing the required data to create a shopping list.

**Return Value:**
- `RedirectResponse`: Redirects to the newly created shopping list's show page, with a success message.

**Functionality:**  
This method creates a new shopping list in the database with the validated data from the request. It then redirects the user to the newly created shopping list's detail page and provides feedback confirming that the list was successfully created.

### update

```php
public function update(UpdateShoppingListRequest $request, ShoppingList $shoppingList): RedirectResponse
```

**Purpose:**  
Updates the attributes of an existing shopping list.

**Parameters:**
- `UpdateShoppingListRequest $request`: Validated request containing the updated data for the shopping list.
- `ShoppingList $shoppingList`: The shopping list to be updated.

**Return Value:**
- `RedirectResponse`: Redirects to the shopping lists index page, with a success message.

**Functionality:**  
The method checks if the user is authorized to update the specified shopping list. If authorized, it updates the shopping list's attributes using the data from the validated request. It then redirects back to the shopping list index with a success message indicating that the update was successful.

### destroy

```php
public function destroy(ShoppingList $shoppingList): RedirectResponse
```

**Purpose:**  
Deletes a specified shopping list.

**Parameters:**
- `ShoppingList $shoppingList`: The shopping list to be deleted.

**Return Value:**
- `RedirectResponse`: Redirects to the shopping lists index page, with a success message.

**Functionality:**  
This method authorizes the user to ensure they can delete the specified shopping list. Upon successful authorization, it deletes the shopping list from the database (along with its associated items due to cascading deletes) and redirects the user to the shopping lists index, providing confirmation of deletion.

## Conclusion

The `ShoppingListController` plays a crucial role in managing user shopping lists, enforcing security through authorization, and interacting with the database through the Shopping List model. Developers working with this controller should be familiar with Laravel's Inertia and resource handling, as well as the associated request validation classes used within the methods.