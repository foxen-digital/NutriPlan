import { beforeEach, describe, expect, it, vi } from 'vitest';

const { mockUsePage, mockToast } = vi.hoisted(() => ({
    mockUsePage: vi.fn(),
    mockToast: vi.fn(),
}));

vi.mock('../../echo', () => ({}));
vi.mock('@/components/ui/toast', () => ({
    useToast: () => ({ toast: mockToast }),
}));
vi.mock('@inertiajs/vue3', () => ({
    usePage: mockUsePage,
}));

describe('useShoppingListSync', () => {
    let useShoppingListSync: typeof import('../useShoppingListSync').useShoppingListSync;
    let mockEchoListen: ReturnType<typeof vi.fn>;
    let mockEchoPrivate: ReturnType<typeof vi.fn>;

    beforeEach(async () => {
        vi.clearAllMocks();

        mockUsePage.mockReturnValue({
            props: { auth: { user: { id: 1 } } },
        });

        mockEchoListen = vi.fn();
        mockEchoPrivate = vi.fn(() => ({ listen: mockEchoListen }));
        (window as any).Echo = { private: mockEchoPrivate };

        // Reset modules to clear the module-level `shoppingListListenersInitialized` flag
        vi.resetModules();
        const module = await import('../useShoppingListSync');
        useShoppingListSync = module.useShoppingListSync;
    });

    it('does not initialize listener when userId is not available', () => {
        mockUsePage.mockReturnValue({ props: { auth: { user: null } } });

        const { initializeListeners } = useShoppingListSync();
        initializeListeners();

        expect(mockEchoPrivate).not.toHaveBeenCalled();
    });

    it('initializes Echo listener on the correct private channel', () => {
        const { initializeListeners } = useShoppingListSync();
        initializeListeners();

        expect(mockEchoPrivate).toHaveBeenCalledWith('user.1');
        expect(mockEchoListen).toHaveBeenCalledWith('.shopping.list.updated', expect.any(Function));
    });

    it('shows toast with correct options when event is received', () => {
        const { initializeListeners } = useShoppingListSync();
        initializeListeners();

        const callback = mockEchoListen.mock.calls[0][1];
        callback({ message: 'Chicken added to your list', shoppingListId: 42 });

        expect(mockToast).toHaveBeenCalledWith({
            title: 'Shopping List Updated',
            description: 'Chicken added to your list',
            variant: 'success',
            duration: 3000,
        });
    });

    it('shows fallback message when event payload message is missing', () => {
        const { initializeListeners } = useShoppingListSync();
        initializeListeners();

        const callback = mockEchoListen.mock.calls[0][1];
        callback({ shoppingListId: 42 });

        const toastCall = mockToast.mock.calls[0][0];
        expect(toastCall.description).toBeDefined();
        expect(typeof toastCall.description).toBe('string');
        expect(toastCall.description.length).toBeGreaterThan(0);
    });

    it('prevents duplicate listener initialization (module-level flag)', () => {
        const { initializeListeners } = useShoppingListSync();
        initializeListeners();
        initializeListeners();

        expect(mockEchoPrivate).toHaveBeenCalledTimes(1);
        expect(mockEchoListen).toHaveBeenCalledTimes(1);
    });

    it('shows multiple independent toasts for multiple events', () => {
        const { initializeListeners } = useShoppingListSync();
        initializeListeners();

        const callback = mockEchoListen.mock.calls[0][1];
        callback({ message: 'First update', shoppingListId: 1 });
        callback({ message: 'Second update', shoppingListId: 1 });
        callback({ message: 'Third update', shoppingListId: 2 });

        expect(mockToast).toHaveBeenCalledTimes(3);
        expect(mockToast).toHaveBeenNthCalledWith(1, expect.objectContaining({ description: 'First update' }));
        expect(mockToast).toHaveBeenNthCalledWith(2, expect.objectContaining({ description: 'Second update' }));
        expect(mockToast).toHaveBeenNthCalledWith(3, expect.objectContaining({ description: 'Third update' }));
    });
});
