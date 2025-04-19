# Documentation: filesystems.php

Original file: `config/filesystems.php`

# filesystems.php Documentation

## Table of Contents

- [Introduction](#introduction)
- [Default Filesystem Disk](#default-filesystem-disk)
- [Filesystem Disks](#filesystem-disks)
  - [Local Disk](#local-disk)
  - [Public Disk](#public-disk)
  - [S3 Disk](#s3-disk)
- [Symbolic Links](#symbolic-links)

## Introduction

The `filesystems.php` file is a configuration file in a PHP application powered by the Laravel framework. It defines the filesystem configuration settings for the application, including the default storage disk and various disks that can be used to store files. This file plays a crucial role in file management and storage, enabling seamless file handling through local and cloud solutions.

## Default Filesystem Disk

- **Purpose**: 
  This section allows developers to specify the default filesystem disk that the application will use for file operations.

- **Configuration**:
  ```php
  'default' => env('FILESYSTEM_DISK', 'local'),
  ```
  - **Parameter**: `FILESYSTEM_DISK` - This environment variable can be set to define the default disk. If not set, it defaults to 'local'.
  - **Return Value**: Returns the name of the disk to be used as the default disk for the application.

## Filesystem Disks

This section contains the configuration for various filesystem disks that the application can utilize, facilitating different storage methods.

### Local Disk

- **Purpose**: 
  Handles file storage on the local filesystem.

- **Configuration**:
  ```php
  'local' => [
      'driver' => 'local',
      'root' => storage_path('app/private'),
      'serve' => true,
      'throw' => false,
      'report' => false,
  ],
  ```
  - **Parameters**:
    - `driver`: Specifies the storage driver, which is 'local' in this case.
    - `root`: Defines the root directory for storage, using `storage_path('app/private')`.
    - `serve`: A flag that, when true, allows serving files via a route.
    - `throw`: A flag indicating whether to throw exceptions for failures.
    - `report`: A flag to report errors during file operations.

- **Functionality**: This disk enables the storage of files directly on the server where the application is hosted.

### Public Disk

- **Purpose**: 
  Facilitates storage of publicly accessible files.

- **Configuration**:
  ```php
  'public' => [
      'driver' => 'local',
      'root' => storage_path('app/public'),
      'url' => env('APP_URL').'/storage',
      'visibility' => 'public',
      'throw' => false,
      'report' => false,
  ],
  ```
  - **Parameters**:
    - `driver`: Specifies the storage driver, which is 'local'.
    - `root`: The root directory is set to `storage_path('app/public')`.
    - `url`: Defines the URL where the files will be accessible (for example, https://your-app-url/storage).
    - `visibility`: Sets the visibility level, defaulting to 'public'.
    - `throw`: Indicates whether to throw exceptions for failures.
    - `report`: Indicates whether to report errors during file operations.

- **Functionality**: This allows files stored in the public directory to be accessible over the web.

### S3 Disk

- **Purpose**: 
  Configures integration with Amazon S3 cloud storage service.

- **Configuration**:
  ```php
  's3' => [
      'driver' => 's3',
      'key' => env('AWS_ACCESS_KEY_ID'),
      'secret' => env('AWS_SECRET_ACCESS_KEY'),
      'region' => env('AWS_DEFAULT_REGION'),
      'bucket' => env('AWS_BUCKET'),
      'url' => env('AWS_URL'),
      'endpoint' => env('AWS_ENDPOINT'),
      'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
      'throw' => false,
      'report' => false,
  ],
  ```
  - **Parameters**:
    - `driver`: Specifies the S3 storage driver.
    - `key`: AWS access key for authentication.
    - `secret`: AWS secret key for authentication.
    - `region`: The AWS region where the bucket is located.
    - `bucket`: The name of the S3 bucket.
    - `url`: The URL for accessing files on S3.
    - `endpoint`: Endpoint for the S3 service.
    - `use_path_style_endpoint`: A flag indicating whether to use path-style URLs for accessing S3 objects.
    - `throw`: Indicates whether to throw exceptions for failures.
    - `report`: Indicates whether to report errors during file operations.

- **Functionality**: This configuration allows the application to read and write files to Amazon S3, providing scalable cloud storage.

## Symbolic Links

- **Purpose**: 
  Allows the app to create symbolic links for easy access to storage files.

- **Configuration**:
  ```php
  'links' => [
      public_path('storage') => storage_path('app/public'),
  ],
  ```
  - **Parameters**:
    - **Key** (location of the link): Defines where the symbolic link will be created. In this case, `public_path('storage')`.
    - **Value** (target location): Defines the target directory of the symbolic link, which is `storage_path('app/public')`.

- **Functionality**: This creates a symbolic link in the public directory that points to the storage's public directory, allowing web access to the files stored there. This is particularly useful for serving assets like images, documents, or other public files without manually copying them to the public directory.

This documentation serves as a comprehensive overview of the `filesystems.php` configuration file, allowing developers to understand its structure, purpose, and individual disk configurations. By following this guide, developers can effectively manage file storage in their Laravel applications.