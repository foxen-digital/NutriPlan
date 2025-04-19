# Documentation: broadcasting.php

Original file: `config/broadcasting.php`

# broadcasting.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Configuration Options](#configuration-options)
  - [Default Broadcaster](#default-broadcaster)
  - [Broadcast Connections](#broadcast-connections)
    - [Reverb Connection](#reverb-connection)
    - [Pusher Connection](#pusher-connection)
    - [Ably Connection](#ably-connection)
    - [Log Connection](#log-connection)
    - [Null Connection](#null-connection)

## Introduction
The `broadcasting.php` configuration file is integral to the Laravel framework’s broadcasting system, which allows developers to push real-time updates and events to clients. This file enables the configuration of different broadcasting drivers and their respective settings, providing flexibility to broadcast events over various channels or services. This configuration file ensures that the correct settings for each broadcasting connection are accessible during runtime.

## Configuration Options

### Default Broadcaster
```php
'default' => env('BROADCAST_CONNECTION', 'null'),
```
- **Purpose**: This option determines the default broadcaster used by the framework when an event needs to be broadcast.
- **Parameters**:
  - **env('BROADCAST_CONNECTION')**: This retrieves the broadcasting connection name from the environment variables. If not set, it defaults to `null`.
- **Return Value**: A string representing the name of the default broadcast driver.
- **Supported Drivers**:
  - `"reverb"`
  - `"pusher"`
  - `"ably"`
  - `"redis"`
  - `"log"`
  - `"null"`

### Broadcast Connections
This section defines all available broadcast connections that can be utilized to broadcast events. Each connection type allows for different configurations for compatibility with various broadcasting services.

```php
'connections' => [
    // connection configurations here
],
```

#### Reverb Connection
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
    ],
    'client_options' => [],
],
```
- **Purpose**: Configuration specific for connecting with the Reverb broadcasting service.
- **Parameters**:
  - `driver`: Specify the broadcasting driver, in this case, `reverb`.
  - `key`: The application's key for authentication.
  - `secret`: The application's secret for security.
  - `app_id`: The application's ID for identification.
  - `options`: Additional configuration settings for the connection.
    - `host`: The Reverb service host.
    - `port`: The port number for the service (default is 443).
    - `scheme`: The scheme (HTTP or HTTPS) used.
    - `useTLS`: A boolean determining if TLS should be used.
- **Return Value**: An associative array containing connection settings for Reverb.

#### Pusher Connection
```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
        'port' => env('PUSHER_PORT', 443),
        'scheme' => env('PUSHER_SCHEME', 'https'),
        'encrypted' => true,
        'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
    ],
    'client_options' => [],
],
```
- **Purpose**: Configuration for the Pusher broadcasting service.
- **Parameters**:
  - `driver`: The broadcasting driver as `pusher`.
  - `key`, `secret`, `app_id`: Authentication details for connecting to Pusher.
  - `options`: Additional parameters including:
    - `cluster`: Pusher application cluster.
    - `host`: The Pusher service host.
    - `port`: The connection port (default 443).
    - `scheme`: HTTP or HTTPS.
    - `encrypted`: A boolean indicating whether the connection should be encrypted.
    - `useTLS`: Automatically set to true if scheme is HTTPS.
- **Return Value**: An associative array with Pusher-specific connection settings.

#### Ably Connection
```php
'ably' => [
    'driver' => 'ably',
    'key' => env('ABLY_KEY'),
],
```
- **Purpose**: Configuration settings for connecting to Ably.
- **Parameters**:
  - `driver`: Set to `ably`.
  - `key`: The API key for Ably for authentication.
- **Return Value**: An associative array containing the key for connection settings.

#### Log Connection
```php
'log' => [
    'driver' => 'log',
],
```
- **Purpose**: Used for logging broadcast messages instead of sending them to a live service.
- **Parameters**:
  - `driver`: Set to `log`.
- **Return Value**: An associative array containing the log configuration.

#### Null Connection
```php
'null' => [
    'driver' => 'null',
],
```
- **Purpose**: This driver does nothing, effectively providing a way to disable broadcasting without causing errors.
- **Parameters**:
  - `driver`: Set to `null` to indicate no broadcasting.
- **Return Value**: An associative array with the null configuration.

## Conclusion
The `broadcasting.php` configuration file serves as the central point for defining how events are broadcasted within the NutriPlan application. Each connection can be customized to fit a variety of third-party broadcasting services, and understanding this configuration allows developers to effectively manage real-time event broadcasts.