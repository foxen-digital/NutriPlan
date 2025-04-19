# Documentation: ImportRecipeRequest.php

Original file: `app/Http/Requests/Recipe/ImportRecipeRequest.php`

# ImportRecipeRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [Methods](#methods)
  - [authorize](#authorize)
  - [rules](#rules)
  - [messages](#messages)

## Introduction
The `ImportRecipeRequest` class is a part of the NutriPlan PHP application, specifically located in the `app/Http/Requests/Recipe` namespace. This class extends the `FormRequest` from the Laravel framework, and its primary purpose is to handle the validation of incoming HTTP requests for importing recipes. The validations ensure that the data provided by users meets predefined criteria before being processed by the application. This class enforces strict type checking and provides user-friendly error messages to enhance the user experience while importing recipes from external URLs.

## Methods

### authorize
```php
public function authorize(): bool
```
#### Purpose
The `authorize` method determines whether the user making the request is authorized to perform the import action.

#### Parameters
- None

#### Return Values
- Returns `true` if the user is authenticated; otherwise, returns `false`.

#### Functionality
The method utilizes the Laravel `auth()` helper to check if the user is logged in. If the user is not authenticated, the application will not proceed with the import request, thereby ensuring that only authorized users can submit recipe import requests.

### rules
```php
public function rules(): array
```
#### Purpose
The `rules` method defines the validation rules that apply to the incoming request data.

#### Parameters
- None

#### Return Values
- Returns an array of validation rules for the incoming request.

#### Functionality
The method specifies that the `url` field is required and must be a valid URL with a maximum length of 2048 characters. Additionally, it includes a custom validation rule that checks if the URL uses either the `http` or `https` protocol. If the URL does not meet these requirements, a failure callback is triggered, returning an appropriate error message to the user.

The validation rules are defined as follows:
- `required`: Ensures the `url` field must be present in the request.
- `url`: Validates that the `url` field contains a properly formatted URL.
- `max:2048`: Sets a maximum character limit for the `url` field.
- Custom callback function that verifies the URL scheme is either `http` or `https`.

### messages
```php
public function messages(): array
```
#### Purpose
The `messages` method returns custom error messages for validation failures.

#### Parameters
- None

#### Return Values
- Returns an array of custom messages keyed by validation rule.

#### Functionality
This method provides user-friendly feedback when validation fails. The messages include:
- For `url.required`: "Please enter a recipe URL."
- For `url.url`: "Please enter a valid URL."
- For `url.max`: "The URL is too long. Maximum length is 2048 characters."

These messages improve the user interface by specifying to the user what exactly went wrong with their input, aiding them in correcting the error accordingly.

## Conclusion
The `ImportRecipeRequest` class plays a crucial role in ensuring that imported recipe data is secure and valid, forming an integral part of the NutriPlan application's HTTP request handling. By implementing stringent validation rules and clear messaging, it supports a robust user experience and maintains the integrity of the application's data processing workflows. Developers should utilize this class to manage incoming requests related to recipe imports effectively and securely.