# Documentation: HandleAppearance.php

Original file: `app/Http/Middleware/HandleAppearance.php`

# HandleAppearance Documentation

## Table of Contents
- [Introduction](#introduction)
- [Method Documentation](#method-documentation)
  - [handle](#handle)

## Introduction

The `HandleAppearance` class is a middleware component in the NutriPlan application, located in the `App\Http\Middleware` namespace. Its primary function is to manage the appearance settings of the application for each incoming HTTP request. This class retrieves an 'appearance' cookie from the user's request and shares its value with all views. If the cookie does not exist, it defaults to 'system'. The middleware ensures that the appearance context is consistently represented across the application by making it available to view templates.

## Method Documentation

### handle

```php
public function handle(Request $request, Closure $next): Response
```

#### Purpose
The `handle` method processes the incoming request by sharing the user's preferred appearance setting with the application's views. It acts as a gatekeeper that runs custom logic before passing the request further along the middleware stack.

#### Parameters
| Parameter | Type                                           | Description                                                  |
|-----------|------------------------------------------------|--------------------------------------------------------------|
| `$request`| `Illuminate\Http\Request`                     | Represents the incoming HTTP request being handled.         |
| `$next`   | `Closure`                                     | A closure that represents the next middleware in the stack. |

#### Return Value
- **Type:** `Symfony\Component\HttpFoundation\Response`
- **Description:** Returns the response from the next middleware or controller after the appearance context has been set.

#### Functionality
- The method first attempts to retrieve the value of a cookie named `appearance` from the incoming request.
- If the cookie exists, its value is used to define the appearance context. If not, it defaults to a value of `'system'`.
- This appearance setting is then shared with all views using the `View::share()` method, which ensures that any view rendered during this request cycle has access to the specified appearance.
- Finally, the request is passed along to the next middleware or controller using the `$next($request)` closure. 

This approach allows for a global appearance setting that can be easily accessed and utilized in any view, thus enhancing the flexibility and user customization of the application's UI. 

### Example Usage
Here is how the middleware might be integrated within an application:

```php
// In your middleware registration (usually in Kernel.php)
protected $middlewareGroups = [
    'web' => [
        // Other middleware...
        \App\Http\Middleware\HandleAppearance::class,
    ],
];
```

In this setup, every HTTP request that goes through the `'web'` middleware group will have the appearance shared with the views, allowing consistent theming across the application based on user preferences or system defaults.