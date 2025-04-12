<?php

declare(strict_types=1);

use App\Services\BarcodeService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->barcodeService = new BarcodeService();
    Config::set('services.barcode.api_key', 'test-api-key');
    Config::set('services.barcode.api_url', 'https://test-api.com/barcode');
});

test('returns product info when barcode is found', function () {
    // Arrange
    $barcode = '12345678901234';
    $mockResponse = [
        'product' => [
            'name' => 'Test Product',
            'category' => 'Test Category',
        ]
    ];

    Http::fake([
        'https://test-api.com/barcode/?query=12345678901234' => Http::response($mockResponse, 200),
    ]);

    // Act
    $result = $this->barcodeService->lookupBarcode($barcode);

    // Assert
    expect($result)->toBe([
        'name' => 'Test Product',
        'category' => 'Test Category',
        'barcode' => $barcode,
    ]);

    Http::assertSent(function ($request) use ($barcode) {
        return $request->url() === "https://test-api.com/barcode/?query={$barcode}" &&
            $request->hasHeader('X-Api-Key', 'test-api-key');
    });
});

test('returns null when product is not found', function () {
    // Arrange
    $barcode = '12345678901234';
    $mockResponse = ['product' => null];

    Http::fake([
        'https://test-api.com/barcode/?query=12345678901234' => Http::response($mockResponse, 200),
    ]);

    // Act
    $result = $this->barcodeService->lookupBarcode($barcode);

    // Assert
    expect($result)->toBeNull();
});

test('returns null on 404 response', function () {
    // Arrange
    $barcode = '12345678901234';

    Http::fake([
        'https://test-api.com/barcode/?query=12345678901234' => Http::response(null, 404),
    ]);

    // Act
    $result = $this->barcodeService->lookupBarcode($barcode);

    // Assert
    expect($result)->toBeNull();
});

test('throws exception on server error', function () {
    // Arrange
    $barcode = '12345678901234';

    Http::fake([
        'https://test-api.com/barcode/?query=12345678901234' => Http::response(null, 500),
    ]);

    // Act & Assert
    expect(fn () => $this->barcodeService->lookupBarcode($barcode))
        ->toThrow(\Exception::class);
});

test('throws exception when API key is not configured', function () {
    // Arrange
    Config::set('services.barcode.api_key', '');
    $barcode = '12345678901234';

    // Act & Assert
    expect(fn () => $this->barcodeService->lookupBarcode($barcode))
        ->toThrow(\Exception::class, 'Barcode API key is not configured.');
});

test('throws exception on request exception')->skip();
