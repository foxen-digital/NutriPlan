# Documentation: HandleInertiaRequests.php

Original file: `app/Http/Middleware/HandleInertiaRequests.php`

# HandleInertiaRequests Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Properties](#class-properties)
- [Methods](#methods)
  - [version](#version)
  - [share](#share)

## Introduction
The `HandleInertiaRequests` class is a middleware component of the PHP application designed for handling Inertia.js requests. Inertia.js is a framework that streamlines the development of single-page applications (SPAs) by providing a way to return server-side rendered views as part of a progressive web application experience. This middleware serves as a bridge between the server and client, allowing the application to manage data, authentication state, and asset versioning seamlessly.

### Role in the System
This middleware handles the initialization of necessary data and settings on HTTP requests, ensuring that critical information is available to the Inertia.js components on the frontend. It also facilitates the passing of shared data that can be accessed by all views, thus promoting code reusability and a consistent user experience.

## Class Properties

| Property      | Type   | Description                                                                                                                      |
|---------------|--------|----------------------------------------------------------------------------------------------------------------------------------|
| `rootView`    | string | The root template name to be loaded on the initial page visit. Default value is `'app'`. This is referenced during the Inertia request lifecycle. |

## Methods

### version

```php
public function version(Request $request): ?string
```

#### Purpose
The `version` method determines the current version of the asset being served. This is particularly useful for cache-busting strategies where clients are served the latest version of assets.

#### Parameters
- `Request $request`: The incoming HTTP request object that contains information about the request.

#### Return Values
- Returns a string representing the asset version or `null` if the version could not be determined.

#### Functionality
This method overrides the parent `version` method from the `Inertia\Middleware` class. It utilizes the parent implementation to return the asset version, ensuring that the versioning logic defined in the parent class is maintained. Versioning helps in serving the most current resources, ensuring users receive updated content after changes are made to the asset files.

### share

```php
public function share(Request $request): array
```

#### Purpose
The `share` method defines the default properties that will be shared across all Inertia.js requests. This is commonly used for passing data necessary for all views.

#### Parameters
- `Request $request`: The incoming HTTP request object that contains information about the request and the authenticated user.

#### Return Values
- Returns an associative array where the keys are the names of the properties and the values are the data being shared.

#### Functionality
The `share` method builds upon the base implementation provided by the parent class. It includes several key shared data points:

- **Application name**: Retrieved from the application configuration.
- **Authenticated user information**: Accessed via `$request->user()`, ensuring the view knows about the current user.
- **Ziggy routes**: Utilizes the `Tighten\Ziggy\Ziggy` class to provide route data needed for client-side routing, including the current URL.
- **Flash messages**: Collects session flash data for various types of notifications (success, error, info, warning, token, status). These values utilize closures to access session data lazily, meaning they are only retrieved when actually needed by the view.

By defining these shared properties, the method enhances the way the application can manage and present shared data, making it accessible throughout the Inertia.js navigation.

---

This documentation outlines the essential aspects of the `HandleInertiaRequests` class, providing the necessary details for developers to understand its purpose, properties, and methods. By utilizing this middleware effectively, developers can ensure a smooth interaction between server and client in Inertia.js applications.