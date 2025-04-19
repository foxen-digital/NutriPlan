# Documentation: MeasurementUnit.php

Original file: `app/Enums/MeasurementUnit.php`

# MeasurementUnit.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [MeasurementUnit Enum](#measurementunit-enum)
  - [Constants](#constants)
  - [Methods](#methods)
    - [label()](#label)
    - [isVolume()](#isvolume)
    - [isWeight()](#isweight)
    - [isUnit()](#isunit)

## Introduction
The `MeasurementUnit.php` file defines an enumeration named `MeasurementUnit` within the `App\Enums` namespace. This enumeration provides a set of constants representing various measurement units commonly used in the context of nutrition and recipe ingredients. Each constant is associated with a string value that denotes its short form, and the enumeration includes methods to categorize and retrieve human-friendly names for each measurement unit.

## MeasurementUnit Enum
The `MeasurementUnit` enum encapsulates several constants and methods, offering both a structured way to handle measurement units and utility functions for validating and retrieving information about these units.

### Constants
The following measurement units are defined as cases within the `MeasurementUnit` enum:

| Constant       | Value |
|----------------|-------|
| GRAM           | `g`   |
| KILOGRAM       | `kg`  |
| MILLILITER     | `ml`  |
| LITER          | `l`   |
| TEASPOON       | `tsp` |
| TABLESPOON     | `tbsp`|
| CUP            | `cup` |
| PIECE          | `pc`  |
| PINCH          | `pinch`|
| CLOVE          | `clove`|

Each constant provides a standardized representation of a measurement unit, making it easier to maintain consistency throughout the application.

### Methods

#### label()
```php
public function label(): string
```
**Purpose:**  
The `label` method returns a human-readable string label for the specific measurement unit represented by the enum instance.

**Parameters:**  
None.

**Return Value:**  
Returns a `string` that is the human-readable name of the measurement unit.

**Functionality:**  
Using the `match` expression in PHP, this method returns the appropriate label based on the current instance of the `MeasurementUnit`. For example, if the instance is `MeasurementUnit::GRAM`, it returns "Gram". This method enhances the usability of the enum by allowing developers to easily retrieve display names, especially when rendering units in user interfaces.

#### isVolume()
```php
public function isVolume(): bool
```
**Purpose:**  
The `isVolume` method checks if the current measurement unit instance represents a volume measurement.

**Parameters:**  
None.

**Return Value:**  
Returns a `bool` indicating whether the measurement unit is a volume (true) or not (false).

**Functionality:**  
This method contains a list of volume measurement units (MILLILITER, LITER, TEASPOON, TABLESPOON, CUP) and checks if the current instance is included in that list. It helps in categorizing measurement units for specific use cases where volume measurements are relevant.

#### isWeight()
```php
public function isWeight(): bool
```
**Purpose:**  
The `isWeight` method checks if the current measurement unit instance represents a weight measurement.

**Parameters:**  
None.

**Return Value:**  
Returns a `bool` indicating whether the measurement unit is a weight (true) or not (false).

**Functionality:**  
Similar to the `isVolume` method, `isWeight` verifies if the current instance of `MeasurementUnit` falls under weight measurements, which include GRAM and KILOGRAM. This functionality aids in enforcing validation rules or in processing logic relevant to weight-based ingredients.

#### isUnit()
```php
public function isUnit(): bool
```
**Purpose:**  
The `isUnit` method checks if the current measurement unit instance represents a unit that is not strictly a volume or weight.

**Parameters:**  
None.

**Return Value:**  
Returns a `bool` indicating whether the measurement unit is a discrete unit (true) or not (false).

**Functionality:**  
This method evaluates whether the current instance corresponds to measurement units like PIECE, PINCH, or CLOVE, which do not fit neatly into volume or weight categorizations. It is useful for enforcing logic around ingredient units that don't have a mass or volume metric.

## Conclusion
The `MeasurementUnit` enum plays a crucial role in simplifying the handling of various measurement units within the application. Its clearly defined constants and utility methods establish a cleaner, more manageable code structure. By providing type-safe enumerations, this class promotes better type checks, reduces errors, and improves the overall maintainability of the codebase surrounding nutritional calculations and recipe representations.