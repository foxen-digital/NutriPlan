# Documentation: BarcodeLookupController.php

Original file: `app/Http/Controllers/Api/BarcodeLookupController.php`

# BarcodeLookupController Documentation

## Table of Contents
- [Introduction](#introduction)
- [Class Declaration](#class-declaration)
- [Methods](#methods)
  - [lookup()](#lookup)
- [Routes](#routes)

## Introduction
The `BarcodeLookupController.php` file contains the `BarcodeLookupController` class, which is responsible for handling API requests related to product lookups via barcodes. This controller integrates with the `BarcodeService` to provide functionalities for validating barcode inputs and retrieving product information based on these barcodes. It ensures that the application maintains proper data integrity and handles various error cases gracefully by returning appropriate HTTP responses.

## Class Declaration
```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
```
The class is declared within the `App\Http\Controllers\Api` namespace and extends the base `Controller` class. It uses several imports including `BarcodeService`, `JsonResponse`, `Request`, `Log`, and `ValidationException`.

## Methods

### lookup()
```php
public function lookup(Request $request): JsonResponse
```

#### Purpose
The `lookup` method handles incoming requests to look up product information based on a given barcode. It validates the request data, interacts with `BarcodeService`, and returns a JSON response with the results or error messages.

#### Parameters
- **Request $request**: The incoming request containing the barcode as a parameter.
  
#### Return Values
- **JsonResponse**: A JSON response containing the outcome of the barcode lookup, which can include:
  - On success: `success` set to true and the `data` containing product information.
  - On failure: `success` set to false with an appropriate `message` and other relevant details (for example, error messages or the barcode).

#### Functionality
1. **Input Validation**:
    - The method starts by validating that the `barcode` input is provided, is a string, and has a length between 3 to 20 characters. If the input validation fails, a `ValidationException` is thrown.
    
2. **Logging**:
   - On successful validation, it logs the lookup attempt using the `Log::info()` method, capturing the barcode being looked up.

3. **Service Interaction**:
   - The method calls the `lookupBarcode` method of the injected `BarcodeService`, passing the validated barcode to fetch related product information.
   
4. **Response Handling**:
   - If the barcode lookup returns `null`, the method responds with a 404 status code, indicating that no product was found for the given barcode.
   - If a valid product is found, it responds with a 200 status code, including the product data in the JSON response.

5. **Error Management**:
    - If a `ValidationException` is caught, it returns a 422 status code with details of validation errors.
    - For any other exceptions, a 500 status code is returned, capturing the error message and logging it for debugging purposes.

### Example Usage
```php
$request = new Request(['barcode' => '123456789012']);
$response = $barcodeLookupController->lookup($request);
```

## Routes
The methods of the `BarcodeLookupController` are typically registered in the API routes file. Here's a typical route declaration for the `lookup` method:
```php
Route::post('/api/barcode-lookup', [BarcodeLookupController::class, 'lookup']);
```
This route defines an endpoint where clients can send POST requests with a barcode in the request body for lookups.

---
This documentation provides an in-depth view of the `BarcodeLookupController` and its method `lookup`. It is intended to assist developers in understanding how the barcode lookup functionality works within the larger context of the application.