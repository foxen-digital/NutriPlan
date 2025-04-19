# Documentation: inertia.php

Original file: `config/inertia.php`

# inertia.php Configuration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Server Side Rendering Configuration](#server-side-rendering-configuration)
- [Testing Configuration](#testing-configuration)

## Introduction
The `inertia.php` file is a configuration file used in the NutriPlan application to set parameters for Inertia.js—a modern framework that allows for building single-page applications (SPAs) using server-side rendering (SSR) with PHP and JavaScript technologies. This file contains configuration settings related to server-side rendering and testing capabilities for Inertia components. By adjusting these parameters, developers can customize the behavior of Inertia within their application.

## Server Side Rendering Configuration

### Purpose
The server-side rendering (`ssr`) configuration block in this file defines settings that dictate how Inertia will handle SSR within the application.

### Configuration Parameters

| Parameter  | Type   | Description                                               |
|------------|--------|-----------------------------------------------------------|
| `enabled`  | bool   | Determines whether server-side rendering is enabled. Default is `false`. |
| `url`      | string | The URL of the server that handles SSR rendering requests. |

### Detailed Functionality
When SSR is enabled, Inertia will make requests to the URL specified in the `url` parameter. This allows Inertia to render pages on the server and send HTML to the client, improving page load times and SEO capabilities. The default configuration has SSR disabled (`enabled: false`).

To enable server-side rendering, set the `enabled` parameter to `true`, and ensure that the server at the specified `url` is properly set up to handle these requests.

Example configuration to enable SSR:
```php
'ssr' => [
    'enabled' => true,
    'url' => 'http://127.0.0.1:13714',
],
```

## Testing Configuration

### Purpose
The `testing` block defines the settings used during the testing of Inertia components, facilitating the verification of component existence and correctness within the identified paths and file types.

### Configuration Parameters

| Parameter                 | Type   | Description                                                                                      |
|---------------------------|--------|--------------------------------------------------------------------------------------------------|
| `ensure_pages_exist`      | bool   | Decides whether tests should verify the existence of Inertia pages. Default is `true`.           |
| `page_paths`              | array  | An array of filesystem paths where Inertia components can be found.                             |
| `page_extensions`         | array  | An array of valid file extensions that can be used for Inertia components.                       |

### Detailed Functionality
When you write tests for your application using the `assertInertia` method, the parameters in this section guide the framework to locate the components. The `page_paths` specifies where to look for the component files, while `page_extensions` defines the acceptable file formats. By default, the configuration checks for component pages in the `resource_path('js/pages')` directory and allows files with the following extensions: `.js`, `.jsx`, `.svelte`, `.ts`, `.tsx`, and `.vue`.

Example configuration for testing components:
```php
'testing' => [
    'ensure_pages_exist' => true,
    'page_paths' => [
        resource_path('js/pages'),
    ],
    'page_extensions' => [
        'js',
        'jsx',
        'svelte',
        'ts',
        'tsx',
        'vue',
    ],
],
```

This configuration helps ensure that all relevant Inertia components are present and correctly implemented before running tests, minimizing runtime errors and improving code quality.

## Conclusion
The `inertia.php` configuration file plays a critical role in managing Inertia.js behavior within an application by providing customizable settings for server-side rendering and testing. Understanding and properly configuring these settings will enhance application performance and testing efficiency.