import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
// Use require to import flush-promises without TS errors
// eslint-disable-next-line @typescript-eslint/no-require-imports
const flushPromises = require('flush-promises');

// Mock the WakeLock API
const mockWakeLock = {
    request: vi.fn(),
};

const mockWakeLockSentinel = {
    release: vi.fn().mockResolvedValue(undefined),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
};

describe('Wake Lock / Cook Mode Functionality', () => {
    // Save original navigator
    let originalNavigator: any;

    // Create our own simplified component to test just the Wake Lock functionality
    const createTestComponent = () => ({
        template: `
      <div>
        <button 
          v-if="isWakeLockSupported" 
          @click="toggleCookMode()" 
          data-test="cook-mode-toggle"
        >
          {{ isCookModeEnabled ? 'Disable' : 'Enable' }} Cook Mode
        </button>
      </div>
    `,
        setup() {
            // eslint-disable-next-line @typescript-eslint/no-require-imports
            const { ref, onMounted, onBeforeUnmount } = require('vue');

            const isWakeLockSupported = ref(false);
            const isCookModeEnabled = ref(false);
            const wakeLock = ref(null as any);

            onMounted(() => {
                // Check if the browser supports the Wake Lock API
                isWakeLockSupported.value = 'wakeLock' in window.navigator && (window.innerWidth <= 1024 || 'ontouchstart' in window);

                // Set up visibility change event listener
                document.addEventListener('visibilitychange', handleVisibilityChange);
            });

            onBeforeUnmount(() => {
                document.removeEventListener('visibilitychange', handleVisibilityChange);
                releaseWakeLock();
            });

            const toggleCookMode = async (state?: boolean) => {
                const newState = state !== undefined ? state : !isCookModeEnabled.value;

                if (newState) {
                    if (window.navigator.wakeLock) {
                        wakeLock.value = await window.navigator.wakeLock.request('screen');
                        isCookModeEnabled.value = true;
                        wakeLock.value.addEventListener('release', () => {
                            isCookModeEnabled.value = false;
                            wakeLock.value = null;
                        });
                    }
                } else {
                    await releaseWakeLock();
                }
            };

            const releaseWakeLock = async () => {
                if (wakeLock.value) {
                    await wakeLock.value.release();
                    wakeLock.value = null;
                    isCookModeEnabled.value = false;
                }
            };

            const handleVisibilityChange = async () => {
                if (document.visibilityState === 'visible' && isCookModeEnabled.value && !wakeLock.value && window.navigator.wakeLock) {
                    wakeLock.value = await window.navigator.wakeLock.request('screen');
                }
            };

            return {
                isWakeLockSupported,
                isCookModeEnabled,
                toggleCookMode,
                releaseWakeLock,
                handleVisibilityChange,
            };
        },
    });

    beforeEach(() => {
        // Save original navigator
        originalNavigator = { ...window.navigator };

        // Mock wakeLock API
        Object.defineProperty(window.navigator, 'wakeLock', {
            value: mockWakeLock,
            configurable: true,
        });

        // Reset mocks
        vi.clearAllMocks();
        mockWakeLock.request = vi.fn().mockResolvedValue(mockWakeLockSentinel);
        mockWakeLockSentinel.release = vi.fn().mockResolvedValue(undefined);
    });

    afterEach(() => {
        // Restore navigator
        delete (window.navigator as any).wakeLock;
        Object.defineProperty(window, 'navigator', { value: originalNavigator, configurable: true });

        // Clear mocks
        vi.restoreAllMocks();
    });

    it('should show the toggle only when Wake Lock API is supported', async () => {
        // Setup for a supported device
        Object.defineProperty(window, 'innerWidth', { value: 390, configurable: true });
        Object.defineProperty(window, 'ontouchstart', { value: {}, configurable: true });

        const wrapper = mount(createTestComponent());
        await nextTick();

        // Toggle should be visible
        expect(wrapper.find('[data-test="cook-mode-toggle"]').exists()).toBe(true);

        // Now test for an unsupported device
        delete (window.navigator as any).wakeLock;
        const wrapper2 = mount(createTestComponent());
        await nextTick();

        // Toggle should not be visible
        expect(wrapper2.find('[data-test="cook-mode-toggle"]').exists()).toBe(false);
    });

    it('should request a wake lock when enabled', async () => {
        // Setup for a supported device
        Object.defineProperty(window, 'innerWidth', { value: 390, configurable: true });
        Object.defineProperty(window, 'ontouchstart', { value: {}, configurable: true });

        const wrapper = mount(createTestComponent());
        await nextTick();

        // Click the toggle button
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // Should have requested a wake lock
        expect(mockWakeLock.request).toHaveBeenCalledWith('screen');

        // Button text should change
        expect(wrapper.find('[data-test="cook-mode-toggle"]').text()).toContain('Disable');
    });

    it('should release the wake lock when disabled', async () => {
        // Setup for a supported device
        Object.defineProperty(window, 'innerWidth', { value: 390, configurable: true });
        Object.defineProperty(window, 'ontouchstart', { value: {}, configurable: true });

        const wrapper = mount(createTestComponent());
        await nextTick();

        // First enable cook mode
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // Then disable it
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // Should have released the wake lock
        expect(mockWakeLockSentinel.release).toHaveBeenCalled();
    });

    it('should release the wake lock when component unmounts', async () => {
        // Setup for a supported device
        Object.defineProperty(window, 'innerWidth', { value: 390, configurable: true });
        Object.defineProperty(window, 'ontouchstart', { value: {}, configurable: true });

        const wrapper = mount(createTestComponent());
        await nextTick();

        // Enable cook mode
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // Clear the call counts
        mockWakeLockSentinel.release.mockClear();

        // Unmount component
        wrapper.unmount();
        await flushPromises();

        // Should have released the wake lock
        expect(mockWakeLockSentinel.release).toHaveBeenCalled();
    });

    it.skip('should re-acquire the wake lock when document becomes visible again', async () => {
        Object.defineProperty(window, 'innerWidth', { value: 390, configurable: true });
        Object.defineProperty(window, 'ontouchstart', { value: {}, configurable: true });

        Object.defineProperty(document, 'visibilityState', { value: 'visible', configurable: true });

        const wrapper = mount(createTestComponent());
        await nextTick();

        // enable
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // disable to release wake lock
        await wrapper.find('[data-test="cook-mode-toggle"]').trigger('click');
        await flushPromises();

        // clear previous calls
        mockWakeLock.request.mockClear();

        // simulate visibility change event
        document.dispatchEvent(new Event('visibilitychange'));
        await flushPromises();

        expect(mockWakeLock.request).toHaveBeenCalledWith('screen');
    });
});
