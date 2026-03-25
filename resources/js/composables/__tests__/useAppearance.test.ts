import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defineComponent, nextTick } from 'vue';

// Must mock matchMedia before importing the composable
const mockMatchMedia = vi.fn().mockReturnValue({
    matches: false,
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
});

vi.stubGlobal('matchMedia', mockMatchMedia);

// Mock localStorage
const localStorageMock = (() => {
    let store: Record<string, string> = {};
    return {
        getItem: (key: string) => store[key] || null,
        setItem: (key: string, value: string) => {
            store[key] = value;
        },
        removeItem: (key: string) => {
            delete store[key];
        },
        clear: () => {
            store = {};
        },
    };
})();

vi.stubGlobal('localStorage', localStorageMock);

import { initializeTheme, updateTheme, useAppearance } from '../useAppearance';

describe('updateTheme', () => {
    beforeEach(() => {
        document.documentElement.classList.remove('dark');
        mockMatchMedia.mockReturnValue({
            matches: false,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
    });

    it('adds dark class when theme is dark', () => {
        updateTheme('dark');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });

    it('removes dark class when theme is light', () => {
        document.documentElement.classList.add('dark');
        updateTheme('light');
        expect(document.documentElement.classList.contains('dark')).toBe(false);
    });

    it('applies dark class based on system preference (dark)', () => {
        mockMatchMedia.mockReturnValue({
            matches: true,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
        updateTheme('system');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });

    it('removes dark class based on system preference (light)', () => {
        document.documentElement.classList.add('dark');
        mockMatchMedia.mockReturnValue({
            matches: false,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
        updateTheme('system');
        expect(document.documentElement.classList.contains('dark')).toBe(false);
    });
});

describe('initializeTheme', () => {
    beforeEach(() => {
        document.documentElement.classList.remove('dark');
        localStorage.clear();
        mockMatchMedia.mockReturnValue({
            matches: false,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
    });

    it('applies dark theme from localStorage', () => {
        localStorage.setItem('appearance', 'dark');
        initializeTheme();
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });

    it('applies light theme from localStorage', () => {
        document.documentElement.classList.add('dark');
        localStorage.setItem('appearance', 'light');
        initializeTheme();
        expect(document.documentElement.classList.contains('dark')).toBe(false);
    });

    it('uses system preference when no localStorage value is set', () => {
        mockMatchMedia.mockReturnValue({
            matches: true,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
        initializeTheme();
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });

    it('registers a system theme change listener', () => {
        const addEventListenerMock = vi.fn();
        mockMatchMedia.mockReturnValue({
            matches: false,
            addEventListener: addEventListenerMock,
            removeEventListener: vi.fn(),
        });
        initializeTheme();
        expect(addEventListenerMock).toHaveBeenCalledWith('change', expect.any(Function));
    });
});

describe('useAppearance', () => {
    beforeEach(() => {
        document.documentElement.classList.remove('dark');
        localStorage.clear();
        mockMatchMedia.mockReturnValue({
            matches: false,
            addEventListener: vi.fn(),
            removeEventListener: vi.fn(),
        });
    });

    afterEach(() => {
        vi.clearAllMocks();
    });

    it('returns appearance defaulting to system', async () => {
        let capturedAppearance: any;
        const TestComponent = defineComponent({
            setup() {
                const { appearance } = useAppearance();
                capturedAppearance = appearance;
                return {};
            },
            template: '<div></div>',
        });

        mount(TestComponent);
        await nextTick();

        expect(capturedAppearance.value).toBe('system');
    });

    it('reads saved appearance from localStorage on mount', async () => {
        localStorage.setItem('appearance', 'dark');
        let capturedAppearance: any;

        const TestComponent = defineComponent({
            setup() {
                const { appearance } = useAppearance();
                capturedAppearance = appearance;
                return {};
            },
            template: '<div></div>',
        });

        mount(TestComponent);
        await nextTick();

        expect(capturedAppearance.value).toBe('dark');
    });

    it('updateAppearance changes the appearance value', async () => {
        let capturedAppearance: any;
        let capturedUpdate: any;

        const TestComponent = defineComponent({
            setup() {
                const { appearance, updateAppearance } = useAppearance();
                capturedAppearance = appearance;
                capturedUpdate = updateAppearance;
                return {};
            },
            template: '<div></div>',
        });

        mount(TestComponent);
        await nextTick();

        capturedUpdate('dark');
        expect(capturedAppearance.value).toBe('dark');
    });

    it('updateAppearance persists value to localStorage', async () => {
        let capturedUpdate: any;

        const TestComponent = defineComponent({
            setup() {
                const { updateAppearance } = useAppearance();
                capturedUpdate = updateAppearance;
                return {};
            },
            template: '<div></div>',
        });

        mount(TestComponent);
        await nextTick();

        capturedUpdate('dark');
        expect(localStorage.getItem('appearance')).toBe('dark');
    });

    it('updateAppearance sets a cookie', async () => {
        let capturedUpdate: any;

        const TestComponent = defineComponent({
            setup() {
                const { updateAppearance } = useAppearance();
                capturedUpdate = updateAppearance;
                return {};
            },
            template: '<div></div>',
        });

        mount(TestComponent);
        await nextTick();

        capturedUpdate('light');
        expect(document.cookie).toContain('appearance=light');
    });
});
