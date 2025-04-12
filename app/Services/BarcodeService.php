<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BarcodeService
{
    /**
     * Lookup a product by barcode using the external API
     *
     * @param string $barcode The barcode to lookup
     * @return array|null The product information or null if not found
     * @throws \Exception If there is an error with the API request
     */
    public function lookupBarcode(string $barcode): ?array
    {
        try {
            $apiKey = config('services.barcode.api_key');
            $apiUrl = config('services.barcode.api_url');

            if (empty($apiKey)) {
                throw new \Exception('Barcode API key is not configured.');
            }

            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
            ])->get("{$apiUrl}/{$barcode}");

            if ($response->successful()) {
                $data = $response->json();

                // Check if product was found
                if (!empty($data['product'])) {
                    return [
                        'name' => $data['product']['name'] ?? 'Unknown Product',
                        'category' => $data['product']['category'] ?? null,
                        'barcode' => $barcode,
                    ];
                }

                // Return null if product not found
                return null;
            }

            if ($response->status() === 404) {
                // Barcode not found in the database
                return null;
            }

            // Other API errors
            throw new \Exception("API Error: " . $response->status() . " - " . $response->body());
        } catch (RequestException $e) {
            Log::error('Barcode lookup error', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
            ]);
            throw new \Exception("Error connecting to barcode service: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Barcode lookup error', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
