# Documentation: IngredientParser.php

Original file: `app/Services/IngredientParser.php`

# IngredientParser Documentation

## Table of Contents
- [Introduction](#introduction)
- [parse Method](#parse-method)

## Introduction
The `IngredientParser.php` file defines the `IngredientParser` class, which is responsible for parsing ingredient strings used in recipes into a structured format. This parsing functionality is crucial for applications that manage recipes, dietary tracking, or culinary databases, where ingredients need to be accurately quantified and categorized.

The class uses regular expressions and predefined patterns to interpret strings such as "1 cup of sugar" or "¼ kg of flour", extracting the amount, unit of measurement, and ingredient name. If the ingredient does not already exist in the database, it creates a new entry in the `Ingredient` model. This functionality enables a robust recipe management system where users can input ingredients in various formats and ensures uniformity in how ingredients are stored and displayed.

## parse Method

### Purpose
The `parse` method processes a single ingredient string and extracts the quantitative information and ingredient name. It cleans up the input string, converts fractions, and determines the appropriate measurement unit before returning the structured data.

### Parameters
- `string $ingredient_string`:
  - This is the input string that represents an ingredient and its quantity (e.g., "2 cups of flour").

### Return Value
- `array`:
  - The method returns an associative array containing:
    - `ingredient`: An instance of the `Ingredient` model corresponding to the parsed name.
    - `amount`: A float value representing the quantity of the ingredient.
    - `unit`: An enum categorized as an instance of `MeasurementUnit` representing the measurement unit.

### Functionality
The `parse` method performs the following steps:

1. **Define Common Unit Patterns**:
   - An associative array `$unit_patterns` maps regex patterns of measurement units to their corresponding constants in the `MeasurementUnit` enum.

2. **Define Fraction Patterns**:
   - An associative array `$fraction_patterns` holds Unicode fraction characters and their decimal equivalents. The method replaces any fractions in the input string with their decimal representations.

3. **Replace Fractions**:
   - Using a loop, the method substitutes the Unicode fractions found in the `$fraction_patterns` into the given `$ingredient_string`.

4. **Extract Amount and Unit**:
   - The method initializes `$amount` to `0`, defaults `$unit` to `MeasurementUnit::PIECE`, and sets `$name` to the original `ingredient_string`.
   - It uses a regular expression to check for numeric values (including fractions) at the start of the string. If a valid match is found, it parses the amount accordingly.

5. **Unit Detection**:
   - A nested loop within the method checks for measurement units following the amount using the `$unit_patterns`. Upon finding a match, it updates the `$unit` and trims the `$name` to exclude the unit.

6. **Cleanup Ingredient Name**:
   - The method cleans the ingredient name by:
     - Trimming whitespace,
     - Removing any leading occurrences of “of”,
     - Eliminating any parts of the string that come after a comma.

7. **Ingredient Lookup/Creation**:
   - Finally, the method checks if an ingredient with the parsed name already exists in the database. If not, it creates a new `Ingredient` record using the `firstOrCreate` method.

8. **Return Structured Data**:
   - The method returns an associative array containing the ingredient object, parsed amount, and unit.

### Example Usage
Here is an example of how this method might be invoked:

```php
$ingredientParser = new IngredientParser();
$result = $ingredientParser->parse("1 ½ cups of flour");

print_r($result);
/*
Output:
Array
(
    [ingredient] => Ingredient instance,
    [amount] => 1.5,
    [unit] => MeasurementUnit::CUP
)
*/
```

This documentation provides a clear understanding of how the `IngredientParser` works, its purpose, and how to utilize its methods effectively within the context of managing ingredients in a recipe application.