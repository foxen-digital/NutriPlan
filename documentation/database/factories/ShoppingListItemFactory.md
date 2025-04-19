# Documentation: ShoppingListItemFactory.php

Original file: `database/factories/ShoppingListItemFactory.php`

# ShoppingListItemFactory Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [definition](#definition)
  - [purchased](#purchased)
  - [notPurchased](#notpurchased)

## Introduction

The `ShoppingListItemFactory` class is part of the database factories used in the NutriPlan application. Its primary purpose is to provide a convenient way to generate fake data for the `ShoppingListItem` model, which represents items in a shopping list. Using this factory, developers can easily create instances of `ShoppingListItem` for testing or seeding purposes. The factory utilizes the Faker library to randomly generate item details, ensuring a diverse set of data for testing scenarios.

## Methods

### definition

```php
public function definition(): array
```

#### Purpose
The `definition` method defines the default state of a `ShoppingListItem` instance. It returns an array of attributes that represent a shopping list item, populating it with randomized, but structured, data.

#### Parameters
- **None**

#### Return Value
- Returns an associative array containing the following attributes:
  - `shopping_list_id` (int): A random identifier for the associated `ShoppingList`, generated through the `ShoppingList` factory.
  - `ingredient_id` (int|null): Currently set to `null`, indicating that specific ingredient information is not utilized.
  - `name` (string): The name of the shopping item, randomly selected from a predefined list.
  - `quantity` (int): The quantity of the item specified in its associated details.
  - `unit` (string): The unit of measurement associated with the quantity of the item.
  - `category` (string): The category of the item (e.g., Dairy, Bakery).
  - `is_custom` (bool): A boolean flag indicating that the item is custom (set to `true`).
  - `is_purchased` (bool): A boolean flag (20% chance to be true) indicating whether the item has been purchased.

#### Functionality
The `definition` method randomly selects an item from a predefined list of grocery items. For each item, it retrieves corresponding quantity, unit, and category data, and then it constructs an array of attributes that represent a `ShoppingListItem`. This array can be easily used to create an instance of the model for testing or database seeding.

### purchased

```php
public function purchased(): static
```

#### Purpose
The `purchased` method states that the shopping list item should be marked as purchased. This method alters the generated attributes to reflect that the item has been bought.

#### Parameters
- **None**

#### Return Value
- Returns an instance of the `ShoppingListItemFactory` class with the updated state.

#### Functionality
This method modifies the `is_purchased` attribute of the `ShoppingListItem` to `true`. It does this by using a state callback that sets the `is_purchased` key in the attributes array. Calling this method will ensure that any item created using this factory reflects the fact that it has been purchased.

### notPurchased

```php
public function notPurchased(): static
```

#### Purpose
The `notPurchased` method specifies that the shopping list item should be marked as not purchased. It allows developers to create items that have not been bought yet.

#### Parameters
- **None**

#### Return Value
- Returns an instance of the `ShoppingListItemFactory` class with the updated state.

#### Functionality
Similar to the `purchased` method, the `notPurchased` method modifies the `is_purchased` attribute of the `ShoppingListItem`, setting it to `false`. This allows for flexibility when creating test data that needs to represent items in various states of purchase.

## Conclusion

The `ShoppingListItemFactory` class is an integral part of the data handling process for the `ShoppingListItem` model within the NutriPlan application. By defining a template for creating shopping list items, it facilitates effective testing and seeding of data. With features to easily declare an item as purchased or not, it supports the diverse scenarios developers might need to simulate in their applications. This factory pattern is essential for maintaining a clean and efficient testing environment, ensuring that the application can be rigorously and meaningfully validated.