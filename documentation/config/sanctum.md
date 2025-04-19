# Documentation: sanctum.php

Original file: `config/sanctum.php`

# sanctum.php Configuration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Configuration Parameters](#configuration-parameters)
  - [Stateful Domains](#stateful-domains)
  - [Sanctum Guards](#sanctum-guards)
  - [Expiration Minutes](#expiration-minutes)
  - [Token Prefix](#token-prefix)
  - [Sanctum Middleware](#sanctum-middleware)

## Introduction

The `sanctum.php` configuration file plays a crucial role in configuring the Laravel Sanctum package, which provides a simple and lightweight authentication system for SPAs (Single Page Applications) and simple APIs. This file outlines the various settings that govern how authentication should behave within an application, specifying details such as stateful domains, guards, token expiration, and middleware.

### Purpose
The primary purpose of this configuration file is to define parameters that determine how users authenticate via API requests, manage session cookies, and handle security measures. It allows developers to adjust the behavior of Sanctum based on the specific needs of their application environments, whether local or production.

## Configuration Parameters

### Stateful Domains

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
)))
```

#### Description
The `stateful` configuration option specifies a list of domains that are allowed to use stateful API authentication. These domains will receive authentication cookies when making requests to the API.

#### Parameters
- **Input**: A comma-separated list of domains defined in the `SANCTUM_STATEFUL_DOMAINS` environment variable. Defaults include common local development environments.

#### Functionality
- The configuration values are retrieved from environment variables and explode them into an array format.
- This allows the application to recognize which domains will maintain session states through the use of cookies.

### Sanctum Guards

```php
'guard' => ['web']
```

#### Description
This option allows you to define the authentication guards that Sanctum will utilize in order to authenticate incoming requests.

#### Parameters
- **Input**: An array of guard names. In this case, `['web']`.

#### Functionality
- When an incoming API request is received, Sanctum will first attempt authentication using the specified guards.
- If none of the guards can authenticate the request, Sanctum will fallback to authenticating based on the bearer token provided in the request.

### Expiration Minutes

```php
'expiration' => null,
```

#### Description
This configuration sets the expiration time (in minutes) for the generated tokens. If not controlled here, tokens will refer to any previously defined "expires_at" attribute.

#### Parameters
- **Input**: An integer defining the expiration time in minutes. Default is `null`, meaning no expiration is enforced on the tokens.

#### Functionality
- By defining an expiration period, developers can manage session durations effectively, enhancing security and access control in their applications.

### Token Prefix

```php
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
```

#### Description
The `token_prefix` setting allows you to set a prefix to generated tokens, which can help prevent accidental exposure of tokens in code repositories.

#### Parameters
- **Input**: A string defined in the `SANCTUM_TOKEN_PREFIX` environment variable.

#### Functionality
- This acts as an additional safety measure, especially in environments where secrecy is paramount. It can be particularly beneficial in repositories where token scanning is enacted.

### Sanctum Middleware

```php
'middleware' => [
    'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
],
```

#### Description
Defines the middleware that Sanctum will use during authentication of requests. Middleware processes requests at various stages in the application lifecycle.

#### Parameters
- **Input**: An associative array where keys represent the middleware names and values represent the middleware class paths.

#### Functionality
- This middleware handles session authentication, cookie encryption, and CSRF protection, ensuring that all requests are processed securely.
- Developers can modify or add middleware as per application requirements, providing flexibility in managing request processing.

---

This detailed documentation of the `sanctum.php` file equips developers with the knowledge to understand and customize the Sanctum authentication configuration effectively for their Laravel applications.