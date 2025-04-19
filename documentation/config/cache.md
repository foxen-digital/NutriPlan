# Documentation: cache.php

Original file: `config/cache.php`

# cache.php Documentation

## Table of Contents
- [Introduction](#introduction)
- [Configuration Overview](#configuration-overview)
  - [Default Cache Store](#default-cache-store)
  - [Cache Stores](#cache-stores)
  - [Cache Key Prefix](#cache-key-prefix)

## Introduction
The `cache.php` file is a configuration file used in PHP applications leveraging the Laravel framework. It serves as the centralized location for defining caching settings, such as the default cache store and various cache stores available in the application. Caching is a crucial aspect of performance optimization in web applications, as it stores frequently accessed data, reducing the need for repeated database queries or complex computations.

## Configuration Overview

### Default Cache Store
```php
'default' => env('CACHE_STORE', 'database')
```
- **Purpose**: This setting specifies which cache store should be used as the default for the application. If no specific store is selected during a cache operation, this default store will be used.
- **Parameters**:
  - `CACHE_STORE`: An environment variable that specifies the default cache store. If not defined, it defaults to `database`.
- **Return Value**: The name of the cache store to be used as the default.

### Cache Stores
The `stores` array defines multiple cache stores and their configurations. Each cache store can have unique settings and can utilize different caching mechanisms.

#### Supported Cache Drivers
The following drivers are supported:
- `array`
- `database`
- `file`
- `memcached`
- `redis`
- `dynamodb`
- `octane`
- `null`

#### Cache Store Configurations
1. **Array Store**
   ```php
   'array' => [
       'driver' => 'array',
       'serialize' => false,
   ],
   ```
   - **Purpose**: This store uses an in-memory array for caching. Data is not persisted when the application terminates.
   - **Parameters**: 
     - `driver`: Identifies this as the array driver.
     - `serialize`: If set to true, the data will be serialized before caching.

2. **Database Store**
   ```php
   'database' => [
       'driver' => 'database',
       'connection' => env('DB_CACHE_CONNECTION'),
       'table' => env('DB_CACHE_TABLE', 'cache'),
       'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
       'lock_table' => env('DB_CACHE_LOCK_TABLE'),
   ],
   ```
   - **Purpose**: This store uses a database table to persist cached data.
   - **Parameters**:
     - `connection`: Environment variable indicating the database connection to use.
     - `table`: The table name where cache data is stored (defaults to `cache`).
     - `lock_connection`: Connection for cache locking.
     - `lock_table`: Table for cache locking.

3. **File Store**
   ```php
   'file' => [
       'driver' => 'file',
       'path' => storage_path('framework/cache/data'),
       'lock_path' => storage_path('framework/cache/data'),
   ],
   ```
   - **Purpose**: Caches data in the file system.
   - **Parameters**:
     - `path`: The directory where cached files will be stored.
     - `lock_path`: The path used for acquiring locks to prevent cache collisions.

4. **Memcached Store**
   ```php
   'memcached' => [
       'driver' => 'memcached',
       'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
       'sasl' => [
           env('MEMCACHED_USERNAME'),
           env('MEMCACHED_PASSWORD'),
       ],
       'options' => [],
       'servers' => [
           [
               'host' => env('MEMCACHED_HOST', '127.0.0.1'),
               'port' => env('MEMCACHED_PORT', 11211),
               'weight' => 100,
           ],
       ],
   ],
   ```
   - **Purpose**: Utilizes Memcached for caching, enabling distributed caching for improved performance.
   - **Parameters**:
     - `persistent_id`: Optional persistent ID.
     - `sasl`: An array containing username and password for authentication.
     - `servers`: An array of server configurations (host, port, weight).

5. **Redis Store**
   ```php
   'redis' => [
       'driver' => 'redis',
       'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
       'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
   ],
   ```
   - **Purpose**: Uses Redis as the caching mechanism.
   - **Parameters**:
     - `connection`: Specifies the Redis connection to use for caching.
     - `lock_connection`: Connection used for cache locking.

6. **DynamoDB Store**
   ```php
   'dynamodb' => [
       'driver' => 'dynamodb',
       'key' => env('AWS_ACCESS_KEY_ID'),
       'secret' => env('AWS_SECRET_ACCESS_KEY'),
       'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
       'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
       'endpoint' => env('DYNAMODB_ENDPOINT'),
   ],
   ```
   - **Purpose**: Leverages AWS DynamoDB for caching data.
   - **Parameters**:
     - `key`: AWS access key ID.
     - `secret`: AWS secret access key.
     - `region`: AWS region for the DynamoDB table.
     - `table`: Table name used for storing cache data.
     - `endpoint`: Custom endpoint for the DynamoDB service.

7. **Octane Store**
   ```php
   'octane' => [
       'driver' => 'octane',
   ],
   ```
   - **Purpose**: Designed for applications using Laravel Octane, providing high-performance caching.
   - **Parameters**: No specific parameters required.

### Cache Key Prefix
```php
'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),
```
- **Purpose**: This configuration helps in preventing cache key collisions when multiple applications use the same caching system.
- **Parameters**:
  - `CACHE_PREFIX`: An environment variable that defines a custom prefix for all cache keys. If not set, it defaults to a slugified version of the application name suffixed with `_cache_`.
  
## Conclusion
The `cache.php` configuration file plays a vital role in defining and managing caching strategies within a Laravel application. By configuring different cache stores and a default cache store, developers can optimize application performance efficiently. Understanding how to customize this file is crucial for leveraging Laravel's caching capabilities effectively.