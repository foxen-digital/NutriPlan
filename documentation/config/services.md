# Documentation: services.php

Original file: `config/services.php`

# services.php Documentation

## Table of Contents
1. [Introduction](#introduction)
2. [Configuration Overview](#configuration-overview)
   - [Postmark](#postmark)
   - [SES (Amazon Simple Email Service)](#ses-amazon-simple-email-service)
   - [Resend](#resend)
   - [Slack Notifications](#slack-notifications)
   - [GitHub Authentication](#github-authentication)
   - [Barcode API](#barcode-api)
   - [OpenAI Integration](#openai-integration)

## Introduction
The `services.php` file serves as the centralized configuration management script for third-party service integrations within the NutriPlan PHP application. It is designed to streamline the way credentials and settings for external services, such as email delivery, messaging, and payment processing, are stored and accessed. 

By convention, this file provides a structured way for various packages and components of the application to retrieve the necessary credentials without the need for hardcoding sensitive information within the application code. Instead, the credentials are pulled from environment variables, which helps maintain security and flexibility when deploying the application across different environments (development, staging, production).

## Configuration Overview

### Postmark
```php
'postmark' => [
    'token' => env('POSTMARK_TOKEN'),
],
```
- **Purpose**: Configures the Postmark service for sending emails.
- **Parameters**:
  - `token` (string): The API token for authenticating with Postmark. Retrieved from the environment variable `POSTMARK_TOKEN`.

### SES (Amazon Simple Email Service)
```php
'ses' => [
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
],
```
- **Purpose**: Configures Amazon SES for sending emails.
- **Parameters**:
  - `key` (string): AWS Access Key ID for accessing the SES service. Retrieved from the environment variable `AWS_ACCESS_KEY_ID`.
  - `secret` (string): AWS Secret Access Key for authenticating requests. Retrieved from the environment variable `AWS_SECRET_ACCESS_KEY`.
  - `region` (string): The region where the SES service is hosted. Defaults to `us-east-1` if not specified in the environment variable `AWS_DEFAULT_REGION`.

### Resend
```php
'resend' => [
    'key' => env('RESEND_KEY'),
],
```
- **Purpose**: Configures the Resend service for email delivery.
- **Parameters**:
  - `key` (string): The API key for Resend, retrieved from the environment variable `RESEND_KEY`.

### Slack Notifications
```php
'slack' => [
    'notifications' => [
        'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
        'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
    ],
],
```
- **Purpose**: This section handles the configuration of Slack notifications.
- **Parameters**:
  - `bot_user_oauth_token` (string): OAuth token for the bot user in Slack, retrieved from the environment variable `SLACK_BOT_USER_OAUTH_TOKEN`.
  - `channel` (string): Default channel for bot notifications, retrieved from `SLACK_BOT_USER_DEFAULT_CHANNEL`.

### GitHub Authentication
```php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URI')
],
```
- **Purpose**: Configuration for GitHub OAuth authentication.
- **Parameters**:
  - `client_id` (string): The Client ID for GitHub authentication, retrieved from `GITHUB_CLIENT_ID`.
  - `client_secret` (string): The Client Secret for GitHub authentication, retrieved from `GITHUB_CLIENT_SECRET`.
  - `redirect` (string): The redirect URI after successful authentication, retrieved from `GITHUB_REDIRECT_URI`.

### Barcode API
```php
'barcode' => [
    'api_key' => env('BARCODE_API_KEY'),
    'api_url' => env('BARCODE_API_URL', 'https://freewebapi.com/api/v1/barcode'),
],
```
- **Purpose**: Configures the Barcode API service for barcode generation and validation.
- **Parameters**:
  - `api_key` (string): API key for authenticating requests to the Barcode API, retrieved from `BARCODE_API_KEY`.
  - `api_url` (string): The URL for the Barcode API. Defaults to `https://freewebapi.com/api/v1/barcode` if not specified in the environment variable `BARCODE_API_URL`.

### OpenAI Integration
```php
'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'model' => env('OPENAI_INGREDIENT_MODEL', 'gpt-4o-mini'),
    // 'organization' => env('OPENAI_ORGANIZATION'), // Optional
],
```
- **Purpose**: Configures access to OpenAI's API for AI-driven functionalities.
- **Parameters**:
  - `api_key` (string): The API key for OpenAI, retrieved from `OPENAI_API_KEY`.
  - `model` (string): Specifies the model to use (default is `gpt-4o-mini`). Retrieved from the environment variable `OPENAI_INGREDIENT_MODEL`.
  - `organization` (string, optional): Organization ID for OpenAI requests, retrieved from `OPENAI_ORGANIZATION`.

## Conclusion

The `services.php` file provides a clear and centralized configuration for managing third-party services essential to the NutriPlan application. Each configuration block is modular, allowing for easy maintenance and updates. By relying on environment variables, developers can seamlessly transition the application's configuration between different environments while maintaining a high level of security.