# Documentation: BarcodeService.php

Original file: `app/Services/BarcodeService.php`

# BarcodeService Documentation

## Table of Contents
- [Introduction](#introduction)
- [lookupBarcode Method](#lookupbarcode-method)

## Introduction
The `BarcodeService` class is an essential component of the NutriPlan application, designed to interact with an external API to retrieve product information based on a provided barcode. This service acts as a bridge between the application and the barcode lookup API, encapsulating the logic required to construct requests, handle responses, and log errors. It is crucial for applications that depend on accurate product information derived from barcodes, enhancing the user experience by providing detailed product data.

## lookupBarcode Method

```php
public function lookupBarcode(string $barcode): ?array
```

### Purpose
The `lookupBarcode` method is responsible for looking up product information by querying an external API with a given barcode. It returns product details if found or null if no information is available.

### Parameters
| Parameter | Type   | Description                                      |
|-----------|--------|--------------------------------------------------|
| `$barcode`| string | The barcode to lookup. This should be a valid barcode string. |

### Return Value
The method returns an associative array containing product details if the product is found; otherwise, it returns `null`. The array structure is as follows:
- `name`: The name or title of the product (string).
- `category`: The category of the product (string|null).
- `barcode`: The original barcode that was queried (string).

### Functionality
1. **Configuration Check**:
   - The method retrieves the API key and URL from the application configuration using the `config` helper.
   - If the API key is not set, an `Exception` is thrown indicating the error.

2. **API Request**:
   - It makes a GET request to the barcode service endpoint using the Laravel `Http` facade, adding the necessary headers.
   - The headers include the API key, ensuring that the request is authenticated.

3. **Response Handling**:
   - If the response is successful (`HTTP 200`):
     - It parses the JSON response to extract product data.
     - Checks if the `product` field is present in the data.
     - Returns an associative array with product details, defaulting to 'Unknown Product' if name or title is not available.
   - If the response status is `404`, indicating that the barcode could not be found, the method returns `null`.

4. **Error Handling**:
   - For non-200 responses, an exception is thrown, detailing the error with the status code and response body.
   - It also catches `RequestException` errors related to HTTP issues, logging relevant information and throwing a user-friendly error message.
   - Any other exceptions are caught, logged, and rethrown, maintaining the stack trace.

### Logging
The method utilizes Laravel's logging features to log any errors encountered during the API request, providing insights for debugging and monitoring purposes.

### Example Usage
```php
$barcodeService = new \App\Services\BarcodeService();
$productInfo = $barcodeService->lookupBarcode('1234567890123');

if ($productInfo !== null) {
    echo 'Product Name: ' . $productInfo['name'];
    echo 'Category: ' . $productInfo['category'];
} else {
    echo 'Product not found.';
}
```

### Conclusion
The `BarcodeService` class provides a robust mechanism for product lookup via barcode, facilitating interaction with external APIs while handling errors gracefully and ensuring that relevant logs are maintained for troubleshooting. Its design follows clean code principles, making it a vital part of the NutriPlan application's architecture.