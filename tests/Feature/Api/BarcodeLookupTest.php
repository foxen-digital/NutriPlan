<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BarcodeLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_barcode_lookup_returns_product_information_when_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        $barcode = '12345678901234';
        $expectedProduct = [
            'name' => 'Test Product',
            'category' => 'Test Category',
            'barcode' => $barcode,
        ];

        // Mock the barcode service
        $mockService = Mockery::mock(BarcodeService::class);
        $mockService->shouldReceive('lookupBarcode')
            ->once()
            ->with($barcode)
            ->andReturn($expectedProduct);
        $this->app->instance(BarcodeService::class, $mockService);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('api.barcode-lookup'), [
                'barcode' => $barcode,
            ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => $expectedProduct,
            ]);
    }

    public function test_barcode_lookup_returns_404_when_product_not_found(): void
    {
        // Arrange
        $user = User::factory()->create();
        $barcode = '12345678901234';

        // Mock the barcode service
        $mockService = Mockery::mock(BarcodeService::class);
        $mockService->shouldReceive('lookupBarcode')
            ->once()
            ->with($barcode)
            ->andReturn(null);
        $this->app->instance(BarcodeService::class, $mockService);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('api.barcode-lookup'), [
                'barcode' => $barcode,
            ]);

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found for this barcode',
                'barcode' => $barcode,
            ]);
    }

    public function test_barcode_lookup_validates_input(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('api.barcode-lookup'), [
                'barcode' => '', // Empty barcode
            ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['barcode']);
    }

    public function test_barcode_lookup_requires_authentication(): void
    {
        // Act
        $response = $this->postJson(route('api.barcode-lookup'), [
            'barcode' => '12345678901234',
        ]);

        // Assert
        $response->assertStatus(401);
    }

    public function test_barcode_lookup_handles_service_exceptions(): void
    {
        // Arrange
        $user = User::factory()->create();
        $barcode = '12345678901234';

        // Mock the barcode service to throw an exception
        $mockService = Mockery::mock(BarcodeService::class);
        $mockService->shouldReceive('lookupBarcode')
            ->once()
            ->with($barcode)
            ->andThrow(new \Exception('Service error'));
        $this->app->instance(BarcodeService::class, $mockService);

        // Act
        $response = $this->actingAs($user)
            ->postJson(route('api.barcode-lookup'), [
                'barcode' => $barcode,
            ]);

        // Assert
        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Error processing barcode lookup',
            ]);
    }
}
