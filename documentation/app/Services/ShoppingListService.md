# Documentation: ShoppingListService.php

Original file: `app/Services/ShoppingListService.php`

# ShoppingListService Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method: generateFromMealPlan](#method-generatefrommealplan)
  - [Parameters](#parameters)
  - [Return Value](#return-value)
  - [Functionality](#functionality)
- [Method: calculatePeriodDates](#method-calculateperioddates)
  - [Parameters](#parameters-1)
  - [Return Value](#return-value-1)
  - [Functionality](#functionality-1)
- [Method: prepareForDisplay](#method-preparefordisplay)
  - [Parameters](#parameters-2)
  - [Return Value](#return-value-2)
  - [Functionality](#functionality-2)

## Introduction
The `ShoppingListService` class is a service layer in the NutriPlan application responsible for generating shopping lists based on specified meal plans and periods. This class encapsulates the logic necessary to create shopping lists with detailed ingredient breakdowns and prepare the data for frontend display.

## Method: generateFromMealPlan
```php
public function generateFromMealPlan(MealPlan $mealPlan, string $name, string $period): ShoppingList
```

### Parameters
| Parameter     | Type      | Description                                      |
|---------------|-----------|--------------------------------------------------|
| `$mealPlan`   | `MealPlan`| The source meal plan that the shopping list will be based on. |
| `$name`       | `string`  | The name for the new shopping list.             |
| `$period`     | `string`  | The period for the shopping list, accepts "full", "week1", or "week2". |

### Return Value
- **Type:** `ShoppingList`
- **Description:** The newly generated shopping list object.

### Functionality
- This method starts by calculating the start and end dates based on the provided meal plan and selected period.
- A new shopping list is created and saved to the database.
- It then retrieves relevant meal assignments that fall within the specified date range and that are marked for cooking (`to_cook=true`).
- The method extracts ingredients from the meal assignments, accounting for their quantities and units. It stores them in a consolidated manner to handle duplicate ingredients with the same unit type.
- Finally, it creates shopping list items in the database for each unique ingredient collected.

## Method: calculatePeriodDates
```php
private function calculatePeriodDates(MealPlan $mealPlan, string $period): array
```

### Parameters
| Parameter | Type      | Description                                      |
|-----------|-----------|--------------------------------------------------|
| `$mealPlan` | `MealPlan`| The meal plan from which to derive the period dates. |
| `$period` | `string`  | The specified period, can be "full", "week1", or "week2". |

### Return Value
- **Type:** `array`
- **Structure:**
    - `start_date` (`Carbon`): The start date for the shopping list period.
    - `end_date` (`Carbon`): The end date for the shopping list period.

### Functionality
- This method computes the start and end dates of the shopping list based on the meal plan's start date and duration.
- Different calculations are performed based on the provided period:
  - For 'week1', it ends one week after the start date.
  - For 'week2', it shifts the start date one week forward.

## Method: prepareForDisplay
```php
public function prepareForDisplay(ShoppingList $shoppingList): array
```

### Parameters
| Parameter     | Type           | Description                                      |
|---------------|----------------|--------------------------------------------------|
| `$shoppingList` | `ShoppingList` | The shopping list that requires formatting for display. |

### Return Value
- **Type:** `array`
- **Description:** An array containing the formatted shopping list data suitable for frontend use.

### Functionality
- This method is responsible for preparing the shopping list data for display in the frontend.
- It loads the associated items while sorting by their order and name.
- It groups items by their categories, ensuring to maintain their order of appearance.
- If all items are uncategorized, it adjusts the display settings accordingly to streamline the interface.
- It formats and organizes the data as necessary for the frontend, including handling cases for categorized and uncategorized items appropriately.

---

This documentation serves as a comprehensive guide to the `ShoppingListService` class, detailing its purpose, methods, and the logic encapsulated within. It provides a clear understanding of how shopping lists are generated from meal plans and how they are prepared for display, facilitating further development and usage of the service within the NutriPlan application.