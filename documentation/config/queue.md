# Documentation: queue.php

Original file: `config/queue.php`

# queue.php Configuration Documentation

## Table of Contents
- [Introduction](#introduction)
- [Default Queue Connection](#default-queue-connection)
- [Queue Connections Configuration](#queue-connections-configuration)
  - [Sync Connection](#sync-connection)
  - [Database Connection](#database-connection)
  - [Beanstalkd Connection](#beanstalkd-connection)
  - [SQS Connection](#sqs-connection)
  - [Redis Connection](#redis-connection)
- [Job Batching Configuration](#job-batching-configuration)
- [Failed Queue Jobs Configuration](#failed-queue-jobs-configuration)

## Introduction
The `queue.php` file is a configuration file for the Laravel framework that governs the behavior of the application's queue system. This file allows developers to define various queue connections, set methods for handling jobs in the queue, and manage failed job logging. By leveraging Laravel's unified API, queues can be seamlessly integrated with different backends such as databases, Redis, SQS, and others. 

## Default Queue Connection
This section defines the default queue connection that will be used by the application when no specific connection is stated. 

```php
'default' => env('QUEUE_CONNECTION', 'database'),
```
- **Purpose**: Specifies which queue driver will be the default when dispatching jobs through the queue system.
- **Parameters**: 
  - Uses the environment variable `QUEUE_CONNECTION`.
  - Defaults to `'database'` if the environment variable is not set.

## Queue Connections Configuration
This section contains settings for various queue connections supported by Laravel. Each connection can be configured with specific options and is identified by a unique name.

### Sync Connection
```php
'sync' => [
    'driver' => 'sync',
],
```
- **Purpose**: Defines a synchronous queuing method where jobs are executed immediately.
- **Parameters**:
  - `driver`: Specifies the queue driver to be 'sync'.
- **Functionality**: No jobs are queued; they are executed in real-time.

### Database Connection
```php
'database' => [
    'driver' => 'database',
    'connection' => env('DB_QUEUE_CONNECTION'),
    'table' => env('DB_QUEUE_TABLE', 'jobs'),
    'queue' => env('DB_QUEUE', 'default'),
    'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
    'after_commit' => false,
],
```
- **Purpose**: Configures the database queue connection for storing jobs in a database.
- **Parameters**:
  - `driver`: Must be 'database'.
  - `connection`: Database connection name (via `DB_QUEUE_CONNECTION`).
  - `table`: Table name for jobs (defaults to 'jobs' via `DB_QUEUE_TABLE`).
  - `queue`: Name of the queue (defaults to 'default' via `DB_QUEUE`).
  - `retry_after`: Number of seconds after which a job may be retried (defaults to 90 seconds).
  - `after_commit`: Determines if the job should be delayed until after the database transaction commits.
- **Functionality**: Manages jobs stored in a specific database table.

### Beanstalkd Connection
```php
'beanstalkd' => [
    'driver' => 'beanstalkd',
    'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
    'queue' => env('BEANSTALKD_QUEUE', 'default'),
    'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
    'block_for' => 0,
    'after_commit' => false,
],
```
- **Purpose**: Configures the Beanstalkd queue driver.
- **Parameters**:
  - `host`: The address of the Beanstalkd server (defaults to `'localhost'`).
  - `queue`: Name of the queue (defaults to 'default').
  - `retry_after`: Time in seconds before a job is retried (defaults to 90).
  - `block_for`: Time in seconds to block and wait for a job.
  - `after_commit`: Determines if the job should be delayed until after the database transaction commits.
- **Functionality**: Allows for handling jobs using Beanstalkd as the message broker.

### SQS Connection
```php
'sqs' => [
    'driver' => 'sqs',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
    'queue' => env('SQS_QUEUE', 'default'),
    'suffix' => env('SQS_SUFFIX'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'after_commit' => false,
],
```
- **Purpose**: Configures the Amazon SQS queue connection.
- **Parameters**:
  - `key`: AWS access key ID.
  - `secret`: AWS secret access key.
  - `prefix`: Prefix URL for the SQS queues.
  - `queue`: Name of the SQS queue (defaults to 'default').
  - `suffix`: Suffix for the queue name.
  - `region`: AWS region for the SQS service (defaults to 'us-east-1').
  - `after_commit`: Determines if the job should be delayed until after the database transaction commits.
- **Functionality**: Allows the application to connect to and use Amazon's Simple Queue Service for managing jobs.

### Redis Connection
```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
    'block_for' => null,
    'after_commit' => false,
],
```
- **Purpose**: Configures the Redis queue connection.
- **Parameters**:
  - `driver`: Must be 'redis'.
  - `connection`: Specifies which Redis connection to use (via `REDIS_QUEUE_CONNECTION`).
  - `queue`: Name of the Redis queue (defaults to 'default').
  - `retry_after`: Time in seconds to wait before retrying a job (defaults to 90).
  - `block_for`: Sets a maximum blocking time for waiting for a job.
  - `after_commit`: Determines if the job should be delayed until after the database transaction commits.
- **Functionality**: Facilitates managing queues using Redis as the backend.

## Job Batching Configuration
```php
'batching' => [
    'database' => env('DB_CONNECTION', 'sqlite'),
    'table' => 'job_batches',
],
```
- **Purpose**: Configures the database settings for storing batch jobs.
- **Parameters**:
  - `database`: The database connection to be used for batch jobs (defaults to 'sqlite').
  - `table`: The table name for storing job batches (