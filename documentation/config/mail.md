# Documentation: mail.php

Original file: `config/mail.php`

# mail.php Configuration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Default Mailer Configuration](#default-mailer-configuration)
- [Mailer Configurations](#mailer-configurations)
  - [SMTP Mailer](#smtp-mailer)
  - [SES Mailer](#ses-mailer)
  - [Postmark Mailer](#postmark-mailer)
  - [Resend Mailer](#resend-mailer)
  - [Sendmail Mailer](#sendmail-mailer)
  - [Log Mailer](#log-mailer)
  - [Array Mailer](#array-mailer)
  - [Failover Mailer](#failover-mailer)
  - [Roundrobin Mailer](#roundrobin-mailer)
- [Global "From" Address Configuration](#global-from-address-configuration)

## Introduction
The `mail.php` file is part of the configuration setup for a PHP application using the Laravel framework. This configuration file defines how the application sends emails, allowing developers to specify various mail transport mechanisms and default settings. It centralizes mail settings, enabling easy modifications to the email delivery method without diving deep into application code.

---

## Default Mailer Configuration
```php
'default' => env('MAIL_MAILER', 'log'),
```

The `default` key specifies which mailer should be used for sending emails by default. It reads its value from the environment variable `MAIL_MAILER`, with a fallback to the `log` mailer. If no explicit mailer is chosen during email transmission, this default is applied.

---

## Mailer Configurations
The `mailers` array contains different configurations for multiple mail transport methods supported by the application. Each entry can be configured independently based on the needs of the application.

### SMTP Mailer
```php
'smtp' => [
    'transport' => 'smtp',
    'scheme' => env('MAIL_SCHEME'),
    'url' => env('MAIL_URL'),
    'host' => env('MAIL_HOST', '127.0.0.1'),
    'port' => env('MAIL_PORT', 2525),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'timeout' => null,
    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
],
```
- **Purpose**: Configures the SMTP mailer to send emails.
- **Parameters**:
  - `transport`: Specifies the type of mailer (smtp).
  - `scheme`: The mail scheme (e.g., `tls`, `ssl`) as defined in the environment variable `MAIL_SCHEME`.
  - `url`: The SMTP server URL to connect to, obtained from `MAIL_URL`.
  - `host`: The SMTP server hostname (default is `127.0.0.1`).
  - `port`: The port on which the SMTP server listens (default is `2525`).
  - `username`: Username for SMTP authentication.
  - `password`: Password for the SMTP user.
  - `timeout`: Duration (in seconds) before timing out (default is null).
  - `local_domain`: Sets the local domain for EHLO/HELO (default is derived from `APP_URL`).

### SES Mailer
```php
'ses' => [
    'transport' => 'ses',
],
```
- **Purpose**: Configures the Amazon SES (Simple Email Service) mailer.
- **Parameters**:
  - `transport`: Specifies the type of mailer (ses). No additional settings are required.

### Postmark Mailer
```php
'postmark' => [
    'transport' => 'postmark',
    // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
    // 'client' => [
    //     'timeout' => 5,
    // ],
],
```
- **Purpose**: Configures the Postmark mailer for transactional emails.
- **Parameters**:
  - `transport`: Specifies the type of mailer (postmark).
  - Additional configurations for `message_stream_id` and `client` are commented out and can be added as needed.

### Resend Mailer
```php
'resend' => [
    'transport' => 'resend',
],
```
- **Purpose**: Configures the Resend mailer.
- **Parameters**:
  - `transport`: Specifies the type of mailer (resend).

### Sendmail Mailer
```php
'sendmail' => [
    'transport' => 'sendmail',
    'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
],
```
- **Purpose**: Configures the sendmail utility for sending emails.
- **Parameters**:
  - `transport`: Specifies the type of mailer (sendmail).
  - `path`: Path to the sendmail executable (default is `/usr/sbin/sendmail -bs -i`).

### Log Mailer
```php
'log' => [
    'transport' => 'log',
    'channel' => env('MAIL_LOG_CHANNEL'),
],
```
- **Purpose**: Sends emails to the log instead of delivering them.
- **Parameters**:
  - `transport`: Specifies the type of mailer (log).
  - `channel`: The logging channel used, defined in `MAIL_LOG_CHANNEL`.

### Array Mailer
```php
'array' => [
    'transport' => 'array',
],
```
- **Purpose**: Stores emails in an array for testing purposes.
- **Parameters**:
  - `transport`: Specifies the type of mailer (array).

### Failover Mailer
```php
'failover' => [
    'transport' => 'failover',
    'mailers' => [
        'smtp',
        'log',
    ],
],
```
- **Purpose**: Attempts to send via multiple mailers sequentially.
- **Parameters**:
  - `transport`: Specifies the type of mailer (failover).
  - `mailers`: An array of mailers to use in the order specified (here using `smtp` and `log`).

### Roundrobin Mailer
```php
'roundrobin' => [
    'transport' => 'roundrobin',
    'mailers' => [
        'ses',
        'postmark',
    ],
],
```
- **Purpose**: Distributes email sending across multiple mailers in a round-robin fashion.
- **Parameters**:
  - `transport`: Specifies the type of mailer (roundrobin).
  - `mailers`: An array of mailers to send emails through alternately (using `ses` and `postmark`).

---

## Global "From" Address Configuration
```php
'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'name' => env('MAIL_FROM_NAME', 'Example'),
],
```
- **Purpose**: Defines a global "from" address for all outgoing emails.
- **Parameters**:
  - `address`: The email