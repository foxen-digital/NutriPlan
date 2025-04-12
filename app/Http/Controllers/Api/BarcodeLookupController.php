<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BarcodeLookupController extends Controller
{
    public function __construct(
        private readonly BarcodeService $barcodeService
    ) {
    }

    /**
     * Look up a product by barcode
     */
    public function lookup(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'barcode' => ['required', 'string', 'min:3', 'max:20']
            ]);

            $barcode = $request->input('barcode');
            Log::info('Looking up barcode: ' . $barcode);
            $result = $this->barcodeService->lookupBarcode($barcode);

            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found for this barcode',
                    'barcode' => $barcode,
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid barcode format',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Barcode lookup failed', [
                'error' => $e->getMessage(),
                'barcode' => $request->input('barcode'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing barcode lookup',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
