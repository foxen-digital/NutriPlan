# Documentation: FetchRecipe.php

Original file: `app/Actions/FetchRecipe.php`

# FetchRecipe Documentation

## Table of Contents
- [Introduction](#introduction)
- [handle Method](#handle-method)

## Introduction

The `FetchRecipe` class is a component of the NutriPlan application that is responsible for retrieving and parsing recipe data from web pages. Utilizing several structured data parsers, it extracts relevant information from the HTML content of a recipe URL provided as input. This class integrates HTTP handling and structured data reading to ensure that recipes can be imported consistently and correctly, enhancing the application's ability to display and manipulate recipe data.

## handle Method

### Purpose
The `handle` method retrieves a recipe from a given URL, processes the HTML content to extract structured recipe data, and returns a `Recipe` model instance.

### Parameters
| Parameter   | Type   | Description                                                  |
|-------------|--------|--------------------------------------------------------------|
| `$recipe_url`| string | The URL of the web page containing the recipe to be fetched. |

### Return Value
The method returns an instance of `Recipe`. If the recipe data cannot be retrieved, it throws an exception.

### Functionality
The `handle` method performs the following steps:

1. **HTTP Request**: 
    - It sends an HTTP GET request to the specified recipe URL using Laravel's HTTP client.
    - It sets a timeout of 10 seconds and a user agent string to mimic a browser request.
  
    ```php
    $response = Http::timeout(10)
        ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36')
        ->get($recipe_url);
    ```

2. **Success Check**: 
    - The method checks if the request was successful. If not, it throws a `ConnectionFailedException` with the HTTP status of the response.
  
    ```php
    if (!$response->successful()) {
        throw new ConnectionFailedException($recipe_url, "HTTP {$response->status()}");
    }
    ```

3. **HTML Encoding**: 
    - It converts the encoding of the response body to HTML entities to handle special characters correctly.
  
    ```php
    $html = mb_convert_encoding($response->body(), 'HTML-ENTITIES', 'UTF-8');
    ```

4. **Data Parsing**:
    - The method attempts to extract recipe data using three different structured data parsers: `JsonLdReader`, `MicrodataReader`, and `RdfaLiteReader`. 
    - If a parser successfully retrieves recipe data, it uses the `RecipeParser` service to create a `Recipe` instance and return it.

    ```php
    $parsers = [
        new JsonLdReader(),
        new MicrodataReader(),
        new RdfaLiteReader(),
    ];
    
    foreach ($parsers as $parser) {
        try {
            $items = (new HTMLReader($parser))->read($html, $recipe_url);
            if (($recipe = RecipeParser::fromItems($items, $recipe_url)) instanceof \App\Models\Recipe) {
                return $recipe;
            }
        } catch (Throwable $e) {
            // Logging the error during parsing
        }
    }
    ```

5. **Error Handling**: 
    - If no parser can successfully retrieve structured data, the method throws a `NoStructuredDataException`.
    - The method also has a general error-catching block that logs any other thrown exceptions as errors and rethrows a `ConnectionFailedException`.

    ```php
    throw new NoStructuredDataException($recipe_url);
    ```

### Summary
The `handle` method in the `FetchRecipe` class plays a crucial role in the NutriPlan application, enabling robust recipe retrieval from various web sources. By systematically attempting multiple parsing strategies and handling errors elegantly, this method ensures a solid foundation for recipe import functionality within the application.