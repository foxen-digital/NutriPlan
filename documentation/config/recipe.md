# Documentation: recipe.php

Original file: `config/recipe.php`

# recipe.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Measurement Units Configuration](#measurement-units-configuration)

## Introduction
The `recipe.php` file is a configuration file within the NutriPlan PHP application. It is responsible for defining the various measurement units that can be used for ingredients in recipes. This file is essential for standardizing ingredient measurements across the application, ensuring consistency when users input or view recipe information.

The configuration is structured as an array that holds key-value pairs, where each measurement unit has a corresponding database value and a label for display in the user interface. By centralizing these definitions, the application can easily reference and manage measurement units as needed.

## Measurement Units Configuration
The `measurement_units` key within the returned array contains an array of measurement unit definitions. Each unit is specified as an associative array that includes:

- **Value**: The identifier used in the database (e.g., `g`, `kg`, `ml`).
- **Label**: A human-readable string that is displayed to users (e.g., `Grams`, `Kilograms`, `Milliliters`).

The following table lists the measurement units defined in the configuration:

| Value  | Label         |
|--------|---------------|
| `g`    | Grams         |
| `kg`   | Kilograms     |
| `ml`   | Milliliters   |
| `l`    | Liters        |
| `tsp`  | Teaspoons     |
| `tbsp` | Tablespoons   |
| `cup`  | Cups          |
| `piece`| Pieces        |
| `pinch`| Pinch         |
| `oz`   | Ounces        |
| `lb`   | Pounds        |
| `slice`| Slices        |
| `clove`| Cloves        |
| `whole`| Whole         |

### Example Usage
The configuration can be utilized throughout the application for displaying measurement units dynamically. For instance, when a user is asked to input the unit of measurement for an ingredient, the application can pull the labels from this configuration to present a user-friendly option chooser.

```php
$measurementUnits = require '/home/mrdth/Development/NutriPlan/ai-recipe-thing/config/recipe.php';
foreach ($measurementUnits['measurement_units'] as $unit) {
    echo "{$unit['label']} ({$unit['value']})\n";
}
```

This code snippet demonstrates how to access measurement units defined in the `recipe.php` file, allowing developers to integrate these configurations into various areas of the user's experience.

## Conclusion
The `recipe.php` file plays an integral role in the functioning of the NutriPlan application by managing the measurement units for recipe ingredients. Keeping this configuration centralized allows for scalability and maintainability as the application evolves, ensuring that any updates to measurement units can be made effortlessly without disrupting the overall application logic. This structured approach helps maintain accuracy in ingredient representation, leading to a better user experience.