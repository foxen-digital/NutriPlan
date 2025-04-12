<?php

namespace Tests\Unit\Services;

use App\Services\BarcodeService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class BarcodeServiceTest extends TestCase
{
    private BarcodeService $barcodeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->barcodeService = new BarcodeService();

        // Configure the barcode API settings
        Config::set('services.barcode.api_key', 'test-api-key');
        Config::set('services.barcode.api_url', 'https://test-api.com/barcode');
    }

    public function test_lookupBarcode_returns_product_info_when_found(): void
    {
        // Arrange
        $barcode = '12345678901234';
        $mockResponse = [
            'product' => [
                'name' => 'Test Product',
                'category' => 'Test Category',
            ]
        ];

        Http::fake([
            'https://test-api.com/barcode/12345678901234' => Http::response($mockResponse, 200),
        ]);

        // Act
        $result = $this->barcodeService->lookupBarcode($barcode);

        // Assert
        $this->assertEquals([
            'name' => 'Test Product',
            'category' => 'Test Category',
            'barcode' => $barcode,
        ], $result);

        Http::assertSent(function ($request) use ($barcode) {
            return $request->url() === "https://test-api.com/barcode/{$barcode}" &&
                $request->hasHeader('X-Api-Key', 'test-api-key');
        });
    }

    public function test_lookupBarcode_returns_null_when_product_not_found(): void
    {
        // Arrange
        $barcode = '12345678901234';
        $mockResponse = ['product' => null];

        Http::fake([
            'https://test-api.com/barcode/12345678901234' => Http::response($mockResponse, 200),
        ]);

        // Act
        $result = $this->barcodeService->lookupBarcode($barcode);

        // Assert
        $this->assertNull($result);
    }

    public function test_lookupBarcode_returns_null_on_404_response(): void
    {
        // Arrange
        $barcode = '12345678901234';

        Http::fake([
            'https://test-api.com/barcode/12345678901234' => Http::response(null, 404),
        ]);

        // Act
        $result = $this->barcodeService->lookupBarcode($barcode);

        // Assert
        $this->assertNull($result);
    }

    public function test_lookupBarcode_throws_exception_on_server_error(): void
    {
        // Arrange
        $barcode = '12345678901234';

        Http::fake([
            'https://test-api.com/barcode/12345678901234' => Http::response(null, 500),
        ]);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->barcodeService->lookupBarcode($barcode);
    }

    public function test_lookupBarcode_throws_exception_when_api_key_not_configured(): void
    {
        // Arrange
        Config::set('services.barcode.api_key', '');
        $barcode = '12345678901234';

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Barcode API key is not configured.');
        $this->barcodeService->lookupBarcode($barcode);
    }

    public function test_lookupBarcode_throws_exception_on_request_exception(): void
    {
        // Arrange
        $barcode = '12345678901234';

        Http::fake(function () {
            throw new RequestException(
                Mockery::mock(\Illuminate\Http\Client\Response::class),
                'Network error'
            );
        });

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error connecting to barcode service');
        $this->barcodeService->lookupBarcode($barcode);
    }
}
