# Documentation: logging.php

Original file: `config/logging.php`

# logging.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Default Log Channel](#default-log-channel)
- [Deprecations Log Channel](#deprecations-log-channel)
- [Log Channels](#log-channels)
  - [Stack Channel](#stack-channel)
  - [Single Channel](#single-channel)
  - [Daily Channel](#daily-channel)
  - [Slack Channel](#slack-channel)
  - [Papertrail Channel](#papertrail-channel)
  - [Stderr Channel](#stderr-channel)
  - [Syslog Channel](#syslog-channel)
  - [Error Log Channel](#error-log-channel)
  - [Null Channel](#null-channel)
  - [Emergency Channel](#emergency-channel)

## Introduction
The `logging.php` configuration file plays a crucial role in managing logging within a PHP application that utilizes the Laravel framework. This file leverages the Monolog PHP logging library to offer a variety of logging channels, handling different logging strategies and allowing for easy customization of how log messages are recorded. By defining active channels, log levels, and message formatting, developers can effectively monitor application behavior, debug issues, and maintain application performance.

## Default Log Channel
```php
'default' => env('LOG_CHANNEL', 'stack'),
```
The `default` key specifies the log channel that will be used when no specific channel is indicated. This can be configured with an environment variable, and the default value is set to `stack`, which aggregates messages from multiple logging channels.

## Deprecations Log Channel
```php
'deprecations' => [
    'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
    'trace' => env('LOG_DEPRECATIONS_TRACE', false),
],
```
The `deprecations` configuration controls the logging of deprecated PHP features and library calls. It allows developers to prepare their applications for future updates by acknowledging deprecated functionality. 

- **channel**: Specifies the logging channel for deprecation messages, defaulting to `null`.
- **trace**: A boolean option to include or exclude stack traces in deprecation logs, defaulting to `false`.

## Log Channels
The `channels` array defines the various logging channels supported by the application. Each channel represents a different way to log messages.

### Stack Channel
```php
'stack' => [
    'driver' => 'stack',
    'channels' => explode(',', env('LOG_STACK', 'single')),
    'ignore_exceptions' => false,
],
```
- **Purpose**: Aggregates multiple log channels.
- **Parameters**:
  - `driver`: Set to `stack` indicating a stack of channels will be used.
  - `channels`: Configurable via the `LOG_STACK` environment variable; provides a comma-separated list of channels to include in the stack.
  - `ignore_exceptions`: If set to `false`, any exceptions will propagate upwards.

### Single Channel
```php
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'replace_placeholders' => true,
],
```
- **Purpose**: Logs messages to a single file.
- **Parameters**:
  - `driver`: Must be `single`.
  - `path`: The file path where log messages will be stored.
  - `level`: Determines the minimum log level for messages to be recorded.
  - `replace_placeholders`: When set to `true`, placeholders in log messages will be replaced.

### Daily Channel
```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => env('LOG_DAILY_DAYS', 14),
    'replace_placeholders' => true,
],
```
- **Purpose**: Similar to the `single` channel, but rotates logs daily.
- **Parameters**:
  - `driver`: Set to `daily`.
  - `path`: The file path for log storage.
  - `level`: Minimum log level to record.
  - `days`: Number of days to keep log files.
  - `replace_placeholders`: Placeholder replacement option.

### Slack Channel
```php
'slack' => [
    'driver' => 'slack',
    'url' => env('LOG_SLACK_WEBHOOK_URL'),
    'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
    'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
    'level' => env('LOG_LEVEL', 'critical'),
    'replace_placeholders' => true,
],
```
- **Purpose**: Sends log messages to a Slack channel.
- **Parameters**:
  - `driver`: Set to `slack`.
  - `url`: Slack webhook URL for posting messages.
  - `username`: Username under which notifications will appear.
  - `emoji`: Emoji used in Slack notifications.
  - `level`: Minimum log level for messages.
  - `replace_placeholders`: Placeholder handling.

### Papertrail Channel
```php
'papertrail' => [
    'driver' => 'monolog',
    'level' => env('LOG_LEVEL', 'debug'),
    'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
    'handler_with' => [
        'host' => env('PAPERTRAIL_URL'),
        'port' => env('PAPERTRAIL_PORT'),
        'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
    ],
    'processors' => [PsrLogMessageProcessor::class],
],
```
- **Purpose**: Sends logs to Papertrail service.
- **Parameters**:
  - `driver`: Uses `monolog`.
  - `level`: Log level setting.
  - `handler`: Defines the handler class for Papertrail logging.
  - `handler_with`: Array of settings like `host` and `port` for the Papertrail connection.
  - `processors`: List of processors applied to log messages.

### Stderr Channel
```php
'stderr' => [
    'driver' => 'monolog',
    'level' => env('LOG_LEVEL', 'debug'),
    'handler' => StreamHandler::class,
    'handler_with' => [
        'stream' => 'php://stderr',
    ],
    'formatter' => env('LOG_STDERR_FORMATTER'),
    'processors' => [PsrLogMessageProcessor::class],
],
```
- **Purpose**: Logs messages to standard error output.
- **Parameters**:
  - `driver`: Uses `monolog`.
  - `level`: Configurable log level.
  - `handler`: Stream handler directing logs to `stderr`.
  - `formatter`: Allows custom formatting of log messages.
  - `processors`: List of integrated message processors.

### Syslog Channel
```php
'syslog' => [
    'driver' => 'syslog',
    'level' => env