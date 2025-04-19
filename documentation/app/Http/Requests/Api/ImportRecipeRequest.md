# Documentation: ImportRecipeRequest.php

Original file: `app/Http/Requests/Api/ImportRecipeRequest.php`

# ImportRecipeRequest Documentation

## Table of Contents
- [Introduction](#introduction)
- [authorize Method](#authorize)
- [rules Method](#rules)
- [messages Method](#messages)

## Introduction
The `ImportRecipeRequest.php` file is part of the `App\Http\Requests\Api` namespace in the NutriPlan PHP application. This file defines an API request class for importing recipes. By extending the `FormRequest` class provided by Laravel, it incorporates built-in validation and authorization features for HTTP requests. The `ImportRecipeRequest` class is particularly responsible for ensuring that the incoming request for importing a recipe adheres to specific validation rules related to the recipe URL.

## authorize Method
### Purpose
The `authorize` method is used to determine if the user is authorized to make the request. In the context of API requests, authorization is typically managed by middleware.

### Parameters
- None

### Return Values
- `bool`: The method always returns `true` in the context of this implementation, indicating that any authenticated user can invoke this request.

### Functionality
The implementation of this method leverages Laravel's middleware, specifically `auth:sanctum`. This allows for a painless authorization process because this method does not contain any additional logic or checks for user permissions.

```php
public function authorize(): bool
{
    return true;
}
```

## rules Method
### Purpose
The `rules` method specifies the validation rules that apply to the incoming request data. This method is essential for ensuring that the data meets the application’s requirements before processing.

### Parameters
- None

### Return Values
- `array`: An associative array containing the validation rules for the request data. 

### Functionality
This method returns an array with a single key, `'url'`, which is associated with several validation criteria:

- **`required`**: The URL must be present in the request.
- **`url`**: The provided string must be a valid URL format.
- **`max:2048`**: The length of the URL must not exceed 2048 characters.
- Custom Closure: This function checks whether the parsed URL has a valid scheme (either HTTP or HTTPS). If not, an error message is triggered.

```php
public function rules(): array
{
    return [
        'url' => [
            'required',
            'url',
            'max:2048',
            function (string $attribute, mixed $value, callable $fail): void {
                $parsed = parse_url($value);
                if (empty($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'])) {
                    $fail('The URL must use either http or https protocol.');
                }
            },
        ],
    ];
}
```

## messages Method
### Purpose
The `messages` method provides custom error messages for validation failures. These messages enhance the user experience by offering clearer instructions.

### Parameters
- None

### Return Values
- `array`: An associative array that maps validation rule names to custom error messages.

### Functionality
The method defines specific messages to be returned for each validation rule applied to the `url` field:

- **`url.required`**: Message when the URL is missing.
- **`url.url`**: Message when the URL format is invalid.
- **`url.max`**: Message when the URL exceeds the maximum allowed length.

```php
public function messages(): array
{
    return [
        'url.required' => 'Please provide a recipe URL.',
        'url.url' => 'Please provide a valid URL.',
        'url.max' => 'The URL is too long. Maximum length is 2048 characters.',
    ];
}
``` 

This documentation provides a clear understanding of how the `ImportRecipeRequest` class functions within the NutriPlan project, outlining its purpose, how it validates data, and how it communicates errors to users. Developers can easily reference this documentation to integrate or modify the request class as needed.