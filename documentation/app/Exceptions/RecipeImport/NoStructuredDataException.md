# Documentation: NoStructuredDataException.php

Original file: `app/Exceptions/RecipeImport/NoStructuredDataException.php`

# NoStructuredDataException Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class: NoStructuredDataException](#class-nostructureddataexception)
  - [Method: __construct](#method-__construct)

## Introduction
The `NoStructuredDataException.php` file contains the definition of the `NoStructuredDataException` class, which is part of the `App\Exceptions\RecipeImport` namespace. This exception is specifically designed to handle errors related to the absence of structured recipe data when attempting to import recipes from a specified URL. The class extends the base `Exception` class, providing a meaningful error message to indicate that no recipe data could be retrieved from the given page.

## Class: NoStructuredDataException

### Purpose
The `NoStructuredDataException` class is an exception that signifies that no structured recipe data (e.g., Recipe schema, JSON-LD, etc.) could be found on the web page from which a recipe is being imported. This is useful in recipe management systems where structured data is essential for proper recipe parsing and handling.

### Method: __construct
```php
public function __construct(string $url)
```

#### Purpose
The constructor method initializes a new instance of the `NoStructuredDataException` class. It takes a URL as its parameter and constructs an exception message that informs the caller that no recipe data could be found at the specified URL.

#### Parameters
| Parameter | Type   | Description                                                  |
|-----------|--------|--------------------------------------------------------------|
| `$url`    | string | The URL of the page that was attempted to be parsed for recipe data. |

#### Return Values
This method does not return a value; it constructs and throws an exception based on the input parameters.

#### Functionality
- The constructor calls the parent class (`Exception`) constructor with a formatted error message:
  - `"No recipe data could be found on the page: {$url}"`
- This message is generated dynamically based on the provided URL, giving users direct feedback on where the issue occurred.

#### Example Usage
```php
throw new NoStructuredDataException('https://example.com/recipe-page');
```
When this exception is thrown, it can be caught elsewhere in the application, and the message will indicate the URL that contained no structured recipe data. This helps in debugging and provides context to the user or developer regarding the origin of the error. 

Overall, `NoStructuredDataException` enhances error handling related to recipe imports by precisely identifying issues with the structured data extraction process, ultimately making the application more robust and user-friendly.