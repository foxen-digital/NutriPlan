# Documentation: NutritionParser.php

Original file: `app/Services/NutritionParser.php`

# NutritionParser Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [parse](#parse)
  - [mapPropertyName](#mappropertyname)
  - [cleanNutritionValue](#cleannutritionvalue)

## Introduction

The `NutritionParser` class is part of the services layer in the NutriPlan PHP application. Its primary purpose is to parse nutrition information from structured data, either in the form of Item objects or as arrays, particularly handling data formatted according to the Schema.org specifications. This class is essential for converting raw nutrition data into a structured format suitable for storage and manipulation within the application, facilitating tasks like displaying nutrition facts to users or analyzing nutritional information.

## Methods

### `parse`

```php
public function parse(array $values): ?array
```

#### Purpose
The `parse` method analyzes the provided nutrition information, extracting relevant data from either `Item` objects or structured arrays. It transforms this data into a standardized format that can be used throughout the application.

#### Parameters
- `array<int, Item|string|array> $values`: An array that can contain `Item` objects from structured data, strings, or arrays representing nutrition information.

#### Return Values
- Returns `array<string, string>`: A key-value pair array where the keys correspond to property names mapped to database column names and the values are the cleaned nutrition values.
- Returns `null`: If no nutrition data can be extracted from the input.

#### Functionality
1. **Iterate Through Values**: The method iterates through the input array to identify if it's dealing with `Item` objects or plain arrays.
2. **Check Types**: For `Item` instances, it checks if the types indicate nutrition-related data by inspecting the type names.
3. **Extract Properties**: For each valid `Item`, it extracts relevant property names and values, mapping them using the `mapPropertyName` method.
4. **Cleaning Values**: It cleans up the extracted values with the `cleanNutritionValue` method to eliminate redundant text and format them properly.
5. **Return Nutrition Data**: If nutrition data is found, it returns the array of cleaned values. If no data is found, `null` is returned.

### `mapPropertyName`

```php
private function mapPropertyName(string $propertyName): ?string
```

#### Purpose
The `mapPropertyName` method provides a mapping between `schema.org` property names and the database column names used within the application. This mapping is crucial for ensuring that parsed data aligns correctly with the application's data structure.

#### Parameters
- `string $propertyName`: The name of the schema.org property (in lowercase) that needs to be mapped.

#### Return Values
- Returns `string`: The mapped name corresponding to a property.
- Returns `null`: If no mapping exists for the provided property name.

#### Functionality
Utilizes a match expression to determine the appropriate database column name for nutrition properties. For example, the schema property `calories` is mapped to the database column `calories`, while `fatContent` maps to `fat_content`. If no mapping is found, it returns `null`.

### `cleanNutritionValue`

```php
private function cleanNutritionValue(string $type, string $value): string
```

#### Purpose
The `cleanNutritionValue` method refines the nutrition values to create a standardized output by removing extraneous text and ensuring the values are in a consistent format.

#### Parameters
- `string $type`: The nutrition type (e.g., 'calories', 'sodium_content'), used to apply specific cleanup rules.
- `string $value`: The raw nutrition value that needs to be cleaned.

#### Return Values
- Returns `string`: The cleaned-up nutrition value.

#### Functionality
1. **Define Patterns**: It defines regular expression patterns specific to each type of nutrition content to remove unwanted descriptors (such as "grams," "of," or "calories").
2. **Apply Patterns**: For the provided type, the method applies the corresponding regex patterns to the value.
3. **Trim and Format**: After cleansing, it trims any whitespace and checks if units need to be appended based on the type. For example:
   - Calories will have " cal" suffixed.
   - Sodium and cholesterol will have " mg" suffixed.
   - Other values will have " g" suffixed if they are numeric.
   
By following this method, the application can maintain consistency in its nutritional data representation.

---

This documentation should enable developers to understand the usage, purpose, and implementation details of the `NutritionParser` class effectively. The focus on clarity and comprehensiveness ensures that anyone working on or utilizing this codebase will have the necessary context and knowledge to work with it efficiently.