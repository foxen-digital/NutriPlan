import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

interface QuaggaJSResultObject {
    codeResult: {
        code: string;
    };
}

interface ProductInfo {
    name: string;
    category: string | null;
    barcode: string;
}

interface ScanResult {
    success: boolean;
    data?: ProductInfo;
    error?: string;
    barcode?: string;
}

interface UseBarcodeScanner {
    isInitialized: Ref<boolean>;
    isScanning: Ref<boolean>;
    lastDetectedCode: Ref<string | null>;
    lastError: Ref<string | null>;
    hasNativeBarcodeDetection: Ref<boolean>;
    initializeScanner: (videoElement: HTMLVideoElement) => Promise<void>;
    startScanning: () => void;
    stopScanning: () => void;
    cleanupResources: () => void;
    lookupBarcode: (barcode: string) => Promise<ScanResult>;
}

// Import Ref type
import { Ref } from 'vue';

export function useBarcodeScanner(): UseBarcodeScanner {
    const isInitialized = ref(false);
    const isScanning = ref(false);
    const lastDetectedCode = ref<string | null>(null);
    const lastError = ref<string | null>(null);
    const hasNativeBarcodeDetection = ref(false);
    let quagga: any = null;
    let currentStream: MediaStream | null = null;

    // Check if the browser supports the Barcode Detection API
    onMounted(async () => {
        try {
            hasNativeBarcodeDetection.value = 'BarcodeDetector' in window;
        } catch (error) {
            console.error('Failed to check for native barcode detection:', error);
            hasNativeBarcodeDetection.value = false;
        }
    });

    const initializeScanner = async (videoElement: HTMLVideoElement): Promise<void> => {
        try {
            lastError.value = null;

            // Force cleanup if already initialized to ensure fresh start
            if (isInitialized.value) {
                await cleanupResources();
                isInitialized.value = false;
            }

            // If we have native support, use it
            if (hasNativeBarcodeDetection.value) {
                // Implementation for native barcode detection would go here
                // For now, we'll just use Quagga2 as a fallback
            }

            // Fallback to Quagga2
            // Dynamically import Quagga2 only when needed
            try {
                const QuaggaModule = await import('@ericblade/quagga2');
                quagga = QuaggaModule.default;
            } catch (e) {
                console.error('Failed to load Quagga2:', e);
                lastError.value = 'Failed to load barcode scanner library';
                throw new Error('Failed to load barcode scanner library');
            }

            // Request camera access
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
            });

            videoElement.srcObject = stream;
            videoElement.play();

            await quagga.init({
                inputStream: {
                    name: 'Live',
                    type: 'LiveStream',
                    target: videoElement,
                    constraints: {
                        facingMode: 'environment',
                    },
                },
                locator: {
                    patchSize: 'medium',
                    halfSample: true,
                },
                numOfWorkers: navigator.hardwareConcurrency || 4,
                decoder: {
                    readers: ['ean_reader', 'ean_8_reader', 'upc_reader', 'upc_e_reader'],
                },
                locate: true,
            });

            quagga.onDetected((result: QuaggaJSResultObject) => {
                if (result.codeResult.code) {
                    lastDetectedCode.value = result.codeResult.code;
                    // Temporarily stop scanning when a code is detected
                    quagga.pause();

                    // Provide haptic feedback if available
                    if (navigator.vibrate) {
                        navigator.vibrate(200);
                    }
                }
            });

            // Store the stream reference for later cleanup
            currentStream = stream;

            isInitialized.value = true;
        } catch (error) {
            lastError.value = 'Failed to initialize camera: ' + (error instanceof Error ? error.message : String(error));
            console.error('Scanner initialization error:', error);
            throw error;
        }
    };

    const startScanning = (): void => {
        if (isInitialized.value && !isScanning.value) {
            if (quagga) {
                quagga.start();
            }
            isScanning.value = true;
            lastDetectedCode.value = null;
        }
    };

    const stopScanning = (): void => {
        if (isInitialized.value && isScanning.value) {
            if (quagga) {
                quagga.stop();
            }
            isScanning.value = false;
        }
    };

    // Add a new method to do complete cleanup
    const cleanupResources = async (): Promise<void> => {
        // Stop Quagga if running
        if (isScanning.value) {
            stopScanning();
        }

        // Release any existing media streams
        if (currentStream) {
            currentStream.getTracks().forEach((track) => track.stop());
            currentStream = null;
        }

        // Reset state
        isInitialized.value = false;
        isScanning.value = false;
        lastDetectedCode.value = null;
    };

    const lookupBarcode = async (barcode: string): Promise<ScanResult> => {
        try {
            const response = await axios.post(route('api.barcode-lookup'), { barcode });

            return {
                success: true,
                data: response.data.data,
            };
        } catch (error) {
            if (axios.isAxiosError(error) && error.response) {
                // API returned an error response
                if (error.response.status === 404) {
                    return {
                        success: false,
                        error: 'Product not found',
                        barcode,
                    };
                }

                return {
                    success: false,
                    error: error.response.data.message || 'Error looking up barcode',
                    barcode,
                };
            }

            // Network or other error
            return {
                success: false,
                error: 'Network error while looking up barcode',
                barcode,
            };
        }
    };

    // Clean up when the component using this composable is unmounted
    onUnmounted(() => {
        cleanupResources();
        if (quagga) {
            quagga.offDetected();
        }
    });

    return {
        isInitialized,
        isScanning,
        lastDetectedCode,
        lastError,
        hasNativeBarcodeDetection,
        initializeScanner,
        startScanning,
        stopScanning,
        cleanupResources,
        lookupBarcode,
    };
}

export default useBarcodeScanner;
