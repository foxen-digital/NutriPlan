import { beforeEach, describe, expect, it, vi } from 'vitest';

const { mockUsePage, mockToast, mockReload, mockRouterVisit } = vi.hoisted(() => ({
    mockUsePage: vi.fn(),
    mockToast: vi.fn(),
    mockReload: vi.fn(),
    mockRouterVisit: vi.fn(),
}));

vi.mock('../../echo', () => ({}));
vi.mock('@/components/ui/toast', () => ({
    useToast: () => ({ toast: mockToast }),
}));
vi.mock('@inertiajs/vue3', () => ({
    router: { reload: mockReload, visit: mockRouterVisit },
    usePage: mockUsePage,
}));

describe('useInstructionNormalization', () => {
    let useInstructionNormalization: typeof import('../useInstructionNormalization').useInstructionNormalization;
    let mockEchoListen: ReturnType<typeof vi.fn>;
    let mockEchoPrivate: ReturnType<typeof vi.fn>;

    beforeEach(async () => {
        vi.clearAllMocks();

        mockUsePage.mockReturnValue({
            component: 'Recipes/Index',
            props: { auth: { user: { id: 1 } } },
        });

        mockEchoListen = vi.fn();
        mockEchoPrivate = vi.fn(() => ({ listen: mockEchoListen }));
        (window as any).Echo = { private: mockEchoPrivate };

        // Reset modules to clear the module-level `instructionListenersInitialized` flag
        vi.resetModules();
        const module = await import('../useInstructionNormalization');
        useInstructionNormalization = module.useInstructionNormalization;
    });

    const getEventCallback = () => {
        const { initializeListeners } = useInstructionNormalization();
        initializeListeners();

        return mockEchoListen.mock.calls[0][1];
    };

    it('does not initialize listener when userId is not available', () => {
        mockUsePage.mockReturnValue({ component: 'Recipes/Index', props: { auth: { user: null } } });

        const { initializeListeners } = useInstructionNormalization();
        initializeListeners();

        expect(mockEchoPrivate).not.toHaveBeenCalled();
    });

    it('initializes Echo listener on the correct private channel', () => {
        const { initializeListeners } = useInstructionNormalization();
        initializeListeners();

        expect(mockEchoPrivate).toHaveBeenCalledWith('user.1');
        expect(mockEchoListen).toHaveBeenCalledWith('.recipe.instructions.normalized', expect.any(Function));
    });

    it('shows success toast with view action when event is received', () => {
        const callback = getEventCallback();
        callback({
            status: 'success',
            message: "Instructions for 'Chili' have been formatted.",
            recipeId: 42,
            recipeUrl: 'http://localhost/recipes/chili',
        });

        expect(mockToast).toHaveBeenCalledWith(
            expect.objectContaining({
                title: 'Success',
                description: "Instructions for 'Chili' have been formatted.",
                variant: 'success',
                duration: 3000,
                action: expect.objectContaining({ label: 'View Recipe' }),
            })
        );

        mockToast.mock.calls[0][0].action.onClick();
        expect(mockRouterVisit).toHaveBeenCalledWith('http://localhost/recipes/chili');
    });

    it('shows warning toast when the service fell back', () => {
        const callback = getEventCallback();
        callback({
            status: 'warning',
            message: "We couldn't auto-format the instructions for 'Chili'. The original instructions were kept.",
            recipeId: 42,
            recipeUrl: 'http://localhost/recipes/chili',
        });

        expect(mockToast).toHaveBeenCalledWith(
            expect.objectContaining({
                title: 'Notice',
                variant: 'warning',
            })
        );
    });

    it('shows destructive toast without view action when recipe is absent', () => {
        const callback = getEventCallback();
        callback({
            status: 'error',
            message: 'We could not format the instructions for this recipe. The original instructions were kept.',
        });

        const toastCall = mockToast.mock.calls[0][0];
        expect(toastCall.title).toBe('Error');
        expect(toastCall.variant).toBe('destructive');
        expect(toastCall.action).toBeUndefined();
    });

    it('reloads the recipe prop when viewing that recipe on success', () => {
        mockUsePage.mockReturnValue({
            component: 'Recipes/Show',
            props: { auth: { user: { id: 1 } }, recipe: { id: 42 } },
        });

        const callback = getEventCallback();
        callback({ status: 'success', message: 'Formatted.', recipeId: 42, recipeUrl: 'http://localhost/recipes/chili' });

        expect(mockReload).toHaveBeenCalledWith({ only: ['recipe'] });
    });

    it('does not reload when viewing a different recipe', () => {
        mockUsePage.mockReturnValue({
            component: 'Recipes/Show',
            props: { auth: { user: { id: 1 } }, recipe: { id: 99 } },
        });

        const callback = getEventCallback();
        callback({ status: 'success', message: 'Formatted.', recipeId: 42, recipeUrl: 'http://localhost/recipes/chili' });

        expect(mockReload).not.toHaveBeenCalled();
    });

    it('does not reload on warning even when viewing the recipe', () => {
        mockUsePage.mockReturnValue({
            component: 'Recipes/Show',
            props: { auth: { user: { id: 1 } }, recipe: { id: 42 } },
        });

        const callback = getEventCallback();
        callback({
            status: 'warning',
            message: 'Could not format.',
            recipeId: 42,
            recipeUrl: 'http://localhost/recipes/chili',
        });

        expect(mockReload).not.toHaveBeenCalled();
    });

    it('does not reload when on another page', () => {
        const callback = getEventCallback();
        callback({ status: 'success', message: 'Formatted.', recipeId: 42, recipeUrl: 'http://localhost/recipes/chili' });

        expect(mockReload).not.toHaveBeenCalled();
    });

    it('prevents duplicate listener initialization (module-level flag)', () => {
        const { initializeListeners } = useInstructionNormalization();
        initializeListeners();
        initializeListeners();

        expect(mockEchoPrivate).toHaveBeenCalledTimes(1);
        expect(mockEchoListen).toHaveBeenCalledTimes(1);
    });

    it('shows multiple independent toasts for multiple events', () => {
        const callback = getEventCallback();
        callback({ status: 'success', message: 'First formatted.', recipeId: 1, recipeUrl: 'u' });
        callback({ status: 'warning', message: 'Second kept.', recipeId: 2, recipeUrl: 'u' });
        callback({ status: 'error', message: 'Third failed.' });

        expect(mockToast).toHaveBeenCalledTimes(3);
        expect(mockToast).toHaveBeenNthCalledWith(1, expect.objectContaining({ description: 'First formatted.' }));
        expect(mockToast).toHaveBeenNthCalledWith(2, expect.objectContaining({ description: 'Second kept.' }));
        expect(mockToast).toHaveBeenNthCalledWith(3, expect.objectContaining({ description: 'Third failed.' }));
    });
});
