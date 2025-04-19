# Documentation: Measurement.php

Original file: `app/ValueObjects/Measurement.php`

# Measurement Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Definition](#class-definition)
- [Constructor](#constructor)
- [Methods](#methods)
  - [from](#from)
  - [scale](#scale)
  - [applySmartRounding](#applysmartrounding)
  - [jsonSerialize](#jsonserialize)
  - [__toString](#__tostring)
  - [format](#format)

## Introduction

The `Measurement` class is a value object designed to encapsulate a measurement in terms of an amount and a specific measurement unit, leveraging the `MeasurementUnit` enum. This class implements `JsonSerializable` for JSON serialization and `Stringable` for string conversion, allowing measurements to be easily converted to and from common formats used in applications dealing with quantities, such as recipes and food nutrition data. The `Measurement` class offers functionalities for scaling measurements, smart rounding, and formatted string output, enhancing its usability in various contexts within the application.

## Class Definition

```php
namespace App\ValueObjects;

use App\Enums\MeasurementUnit;
use JsonSerializable;

class Measurement implements JsonSerializable, \Stringable
{
    // ...
}
```

## Constructor

```php
public function __construct(
    public readonly ?float $amount = null,
    public readonly ?MeasurementUnit $unit = null,
)
```

### Purpose
The constructor initializes a `Measurement` object with an optional `amount` (float) and an optional `unit` (MeasurementUnit enum).

### Parameters
- `float|null $amount`: The numerical value of the measurement, or null if not specified.
- `MeasurementUnit|null $unit`: The unit of measurement, represented as an instance of the `MeasurementUnit` enum, or null if not specified.

### Return Values
- Returns an instance of the `Measurement` class.

## Methods

### from

```php
public static function from(?float $amount, ?string $unit = null): self
```

#### Purpose
Creates a new `Measurement` instance from a given amount and unit string.

#### Parameters
- `float|null $amount`: The numerical value of the measurement, can be null.
- `string|null $unit`: The unit string to be converted into a MeasurementUnit enum.

#### Return Values
- Returns a new instance of the `Measurement` class.

#### Functionality
- The method first checks if a unit string is provided. If valid, it attempts to create a corresponding `MeasurementUnit` using `tryFrom`. If successful, it returns a new `Measurement` instance with the amount and the unit enum; otherwise, it returns an instance with the amount and null for the unit.

### scale

```php
public function scale(float $factor): self
```

#### Purpose
Scales the measurement by a specified factor.

#### Parameters
- `float $factor`: The factor by which to scale the measurement's amount.

#### Return Values
- Returns a new `Measurement` instance with the scaled amount and the same unit.

#### Functionality
- If the current amount is null, it returns a new Measurement with a null amount.
- It multiplies the amount by the scaling factor and applies smart rounding based on the measurement unit, especially for small and specific units.

### applySmartRounding

```php
private function applySmartRounding(float $amount): float
```

#### Purpose
Applies rounding rules to a numeric amount based on the measurement unit to maintain usability of the result.

#### Parameters
- `float $amount`: The amount to be rounded based on specific rules.

#### Return Values
- Returns the rounded amount as a float.

#### Functionality
- The method includes specificity for types of measurements, applying different rounding strategies based on the unit of the measurement:
  - Preserves exact values for amounts less than 0.1.
  - Rounds to one decimal place for amounts less than 1.
  - Adjusts to fractions for teaspoons, tablespoons, cups, and rounding rules for weight and volume units.

### jsonSerialize

```php
public function jsonSerialize(): array
```

#### Purpose
Provides a way to serialize the Measurement object into a JSON-compatible array.

#### Return Values
- Returns an associative array representation of the Measurement object.

#### Functionality
- This method constructs an array with two keys: `amount` and `unit`, where the unit value is extracted using its enum value.

### __toString

```php
public function __toString(): string
```

#### Purpose
Allows the `Measurement` object to be represented as a string when needed.

#### Return Values
- Returns a formatted string representation of the Measurement object.

#### Functionality
- The method checks if the amount is null and returns the unit value if true. If the unit exists, it formats the string as "amount unit".

### format

```php
public function format(): string
```

#### Purpose
Formats the Measurement object into a user-friendly string.

#### Return Values
- Returns a string that represents the measurement with the appropriate formatting.

#### Functionality
- Similar to `__toString`, but also handles trailing zeros in the numerical representation for more readable output, ensuring that the amount is formatted to two decimal places and removing unnecessary decimal points.

---

This documentation serves as a comprehensive guide to understanding the `Measurement` class in the `NutriPlan` application, detailing its purpose, features, and how to effectively utilize its methods.