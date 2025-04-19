# Documentation: ShoppingListItemPurchaseController.php

Original file: `app/Http/Controllers/ShoppingListItemPurchaseController.php`

# ShoppingListItemPurchaseController Documentation

## Table of Contents
- [Introduction](#introduction)
- [__invoke Method](#__invoke-method)
- [Routes Handled](#routes-handled)
- [Models Documentation](#models-documentation)

## Introduction
The `ShoppingListItemPurchaseController` class is part of the NutriPlan PHP application. It controls the logic behind toggling the purchased status of items in a shopping list. This controller ensures that only authorized users can modify the purchased status of items, preserving security and data integrity. It serves as an endpoint for managing the purchased state of shopping list items, enhancing user experience by allowing users to track which items have been acquired.

## __invoke Method

### Purpose
The `__invoke` method is intended to toggle the purchased status of a specific item within a designated shopping list. It simplifies the management of purchased items by allowing a single controller action to handle requests related to item purchases.

### Parameters
- **ShoppingList $shoppingList**: An instance of the `ShoppingList` model that represents the shopping list to which the item belongs.
- **ShoppingListItem $item**: An instance of the `ShoppingListItem` model that represents the item whose purchased status is being toggled.

### Return Value
- Returns a `RedirectResponse` that redirects the user back to the shopping list view with a success message indicating the item's purchase status.

### Functionality
1. **Authorization**: The method begins by ensuring the user is authorized to perform the update on the shopping list using the `authorize` method. If the user is not authorized, an unauthorized response will be generated.

2. **Ownership Validation**: The method checks if the given shopping list item belongs to the specified shopping list by comparing their IDs. If the IDs do not match, the method aborts the request, returning a 404 error.

3. **Status Toggle**: It toggles the `is_purchased` attribute of the `ShoppingListItem`. If the item was previously marked as purchased (true), it will be updated to not purchased (false) and vice versa.

4. **Response Handling**: After updating the item, it redirects the user to the `shopping-lists.show` route for the specified shopping list and includes a success message indicating the successful change of the item's purchased status.

```php
public function __invoke(ShoppingList $shoppingList, ShoppingListItem $item): RedirectResponse
{
    $this->authorize('update', $shoppingList);

    // Ensure the item belongs to the shopping list
    if ($item->shopping_list_id !== $shoppingList->id) {
        abort(404);
    }

    $item->update([
        'is_purchased' => !$item->is_purchased,
    ]);

    return redirect()->route('shopping-lists.show', $shoppingList)
        ->with('success', 'Item ' . ($item->is_purchased ? 'marked as purchased' : 'marked as not purchased') . '.');
}
```

## Routes Handled
The `ShoppingListItemPurchaseController` handles requests sent to the following route:

- **HTTP Method**: POST
- **Route**: `/shopping-lists/{shoppingList}/items/{item}/toggle-purchased`
  
This route is responsible for delegating to the `__invoke` method, allowing users to toggle the purchased status of an item in a shopping list by making a POST request.

## Models Documentation

### ShoppingList
- **Relationships**:
  - A `ShoppingList` can have multiple `ShoppingListItem` instances associated with it.
  
- **Important Attributes**:
  - `id`: Primary key for identifying the shopping list.
  - `user_id`: Foreign key linked to the user who owns the shopping list.

### ShoppingListItem
- **Relationships**:
  - Each `ShoppingListItem` belongs to one `ShoppingList`.
  
- **Important Attributes**:
  - `id`: Primary key for identifying the shopping list item.
  - `shopping_list_id`: Foreign key linking the item to the corresponding shopping list.
  - `is_purchased`: Boolean attribute representing whether the item has been purchased (true) or not (false).

This documentation provides a clear understanding of the `ShoppingListItemPurchaseController` and its responsibilities within the NutriPlan application. It serves to assist developers in grasping both the functionality of the code and the underlying principles guiding its implementation.