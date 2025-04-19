# Documentation: IngredientNormalizationService.php

Original file: `app/Services/IngredientNormalizationService.php`

# IngredientNormalizationService Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Overview](#class-overview)
- [Method: `__construct`](#method-__construct)
- [Method: `normalize`](#method-normalize)
- [Method: `normalizeWithLlm`](#method-normalizewithllm)
- [Method: `buildPrompt`](#method-buildprompt)
- [Method: `makeLlmRequest`](#method-makellmrequest)
- [Method: `normalizeWithFallback`](#method-normalizewithfallback)
- [Method: `validateNormalizedIngredients`](#method-validatenormalizedingredients)

## Introduction
The `IngredientNormalizationService` class is responsible for normalizing ingredient strings provided as input to a standardized JSON format. This class leverages a large language model (LLM) via the `OpenAiClient` to intelligently parse ingredients and extract useful information such as quantity, unit, and preparation notes. If the LLM fails to return valid results, the service falls back on a more straightforward ingredient parsing strategy implemented by `IngredientParser`. 

The class is defined in the namespace `App\Services` and integrates multiple external and internal dependencies, utilizing Laravel's logging and HTTP client functionalities.

## Class Overview
The `IngredientNormalizationService` consists of a constructor and several methods designed to perform ingredient normalization through LLM and fallback methods, ensuring robust handling of various input scenarios.

## Method: `__construct`
```php
public function __construct(
    private readonly OpenAiClient $openAiClient,
    private readonly IngredientParser $ingredientParser
)
```
### Purpose
The constructor initializes the `IngredientNormalizationService` class with dependencies for LLM interactions and ingredient parsing.

### Parameters
- `OpenAiClient $openAiClient`: An instance of the OpenAI client used for performing requests to the LLM.
- `IngredientParser $ingredientParser`: An instance of the ingredient parser used for fallback parsing in case the LLM fails.

## Method: `normalize`
```php
public function normalize(array $ingredientStrings): array
```
### Purpose
Normalizes an array of ingredient strings into a structured format.

### Parameters
- `array $ingredientStrings`: An array of ingredient strings to be normalized.

### Return Values
- Returns an array of normalized ingredients, or an empty array if no ingredient strings are provided.

### Functionality
The method first checks if the provided array is empty. If so, it returns an empty array. It then attempts to normalize the ingredients using the LLM. In case of any exceptions, it logs the error and falls back to using a simpler parsing method.

## Method: `normalizeWithLlm`
```php
private function normalizeWithLlm(array $ingredientStrings): array
```
### Purpose
Handles normalization of ingredients using the LLM.

### Parameters
- `array $ingredientStrings`: An array of ingredient strings.

### Return Values
- Returns an array of normalized ingredients based on the LLM's response.

### Functionality
1. Builds a prompt for the LLM using `buildPrompt`.
2. Makes an LLM request using `makeLlmRequest`.
3. Validates the structure of the LLM's JSON response.
4. Checks if the number of ingredients returned matches the number of inputs. If discrepancies exist, it uses the fallback method for any missing ingredients.
5. Finally, it validates the normalized ingredients using `validateNormalizedIngredients`.

## Method: `buildPrompt`
```php
private function buildPrompt(array $ingredientStrings): array
```
### Purpose
Constructs the prompt for the LLM based on the input ingredient strings.

### Parameters
- `array $ingredientStrings`: An array of ingredient strings.

### Return Values
- Returns an array that represents the LLM prompt structure, including both system and user roles.

### Functionality
The method encodes the ingredient strings into JSON format and returns a formatted prompt to be sent to the LLM.

## Method: `makeLlmRequest`
```php
private function makeLlmRequest(array $prompt, int $attempt = 1): \Illuminate\Http\Client\Response
```
### Purpose
Sends a request to the LLM and manages potential retries on failure.

### Parameters
- `array $prompt`: The structured prompt prepared for the LLM.
- `int $attempt`: Used to track the attempt count for retries (default is 1).

### Return Values
- Returns a successful HTTP client response from the LLM.

### Functionality
1. Attempts to send a request to the LLM using the provided prompt.
2. Implements exponential backoff strategy in case of a failure with a limit of 3 attempts.
3. Throws a `RequestException` if the maximum attempts are reached without success.

## Method: `normalizeWithFallback`
```php
private function normalizeWithFallback(array $ingredientStrings): array
```
### Purpose
Parses ingredient strings using a simpler fallback mechanism.

### Parameters
- `array $ingredientStrings`: An array of ingredient strings to parse.

### Return Values
- Returns an array of normalized ingredients derived from fallback parsing.

### Functionality
This method logs a warning when the fallback parser is used. It then attempts to parse each ingredient string using the `ingredientParser`. In case of an error for any ingredient, it logs the error and returns the original string as a fallback.

## Method: `validateNormalizedIngredients`
```php
private function validateNormalizedIngredients(array $normalizedIngredients, array $originalStrings): array
```
### Purpose
Validates the structure and completeness of the normalized ingredient arrays.

### Parameters
- `array $normalizedIngredients`: The normalized ingredients to validate.
- `array $originalStrings`: The original strings corresponding to the normalized ingredients.

### Return Values
- Returns an array of validated normalized ingredients.

### Functionality
Each normalized ingredient is checked for the presence of required fields. Any discrepancies are defaulted to the original string. It ensures numeric values for quantity or sets them to null when applicable.

---

This documentation provides a comprehensive overview of the `IngredientNormalizationService`, detailing its functionality and how it interacts with other components of the system. By following these instructions, developers will better understand how to utilize this service for normalizing ingredients effectively.