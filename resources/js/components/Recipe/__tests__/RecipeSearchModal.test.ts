import { shallowMount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import RecipeSearchModal from '../RecipeSearchModal.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        get: vi.fn(),
    },
}));

vi.mock('@/components/ui/dialog', () => ({
    Dialog: { template: '<div><slot /></div>', props: ['open'] },
    DialogContent: { template: '<div><slot /></div>' },
    DialogHeader: { template: '<div><slot /></div>' },
    DialogTitle: { template: '<div><slot /></div>' },
    DialogDescription: { template: '<div><slot /></div>' },
    DialogFooter: { template: '<div><slot /></div>' },
    DialogClose: { template: '<div><slot /></div>' },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        template: '<button @click="$attrs.onClick?.()"><slot /></button>',
        inheritAttrs: false,
    },
}));

vi.mock('@/components/ui/input', () => ({
    Input: { template: '<input />' },
}));

vi.mock('@/components/ui/label', () => ({
    Label: { template: '<label><slot /></label>' },
}));

vi.mock('@/components/ui/radio-group', () => ({
    RadioGroup: { template: '<div><slot /></div>' },
    RadioGroupItem: { template: '<input type="radio" />' },
}));

vi.stubGlobal(
    'route',
    vi.fn((name?: string) => {
        if (!name) return { params: { category: 'main' } };
        return `mocked-route/${name}`;
    }),
);

const defaultProps = {
    open: true,
    currentFilters: {
        search_term: '',
        search_mode: 'name_description',
    },
};

describe('RecipeSearchModal', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders when open', () => {
        const wrapper = shallowMount(RecipeSearchModal, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('initializes searchTerm from currentFilters', () => {
        const wrapper = shallowMount(RecipeSearchModal, {
            props: {
                ...defaultProps,
                currentFilters: { search_term: 'pasta', search_mode: 'name_description' },
            },
        });
        expect((wrapper.vm as any).searchTerm).toBe('pasta');
    });

    it('initializes searchMode from currentFilters', () => {
        const wrapper = shallowMount(RecipeSearchModal, {
            props: {
                ...defaultProps,
                currentFilters: { search_term: '', search_mode: 'ingredient' },
            },
        });
        expect((wrapper.vm as any).searchMode).toBe('ingredient');
    });

    it('hasActiveSearch is true when currentFilters has search_term', () => {
        const wrapper = shallowMount(RecipeSearchModal, {
            props: {
                ...defaultProps,
                currentFilters: { search_term: 'chicken', search_mode: 'name_description' },
            },
        });
        expect((wrapper.vm as any).hasActiveSearch).toBe(true);
    });

    it('hasActiveSearch is false when currentFilters has no search_term', () => {
        const wrapper = shallowMount(RecipeSearchModal, { props: defaultProps });
        expect((wrapper.vm as any).hasActiveSearch).toBe(false);
    });

    it('performSearch calls router.get with correct params', async () => {
        const { router } = await import('@inertiajs/vue3');
        const wrapper = shallowMount(RecipeSearchModal, { props: defaultProps });

        (wrapper.vm as any).searchTerm = 'chicken soup';
        await (wrapper.vm as any).performSearch();

        expect(router.get).toHaveBeenCalledWith(
            'mocked-route/recipes.index',
            expect.objectContaining({
                search_term: 'chicken soup',
            }),
            expect.any(Object),
        );
    });

    it('performSearch removes search params when searchTerm is empty', async () => {
        const { router } = await import('@inertiajs/vue3');
        const wrapper = shallowMount(RecipeSearchModal, { props: defaultProps });

        (wrapper.vm as any).searchTerm = '';
        await (wrapper.vm as any).performSearch();

        const callArgs = vi.mocked(router.get).mock.calls[0][1] as Record<string, unknown>;
        expect(callArgs).not.toHaveProperty('search_term');
    });

    it('clearSearch calls router.get without search params', async () => {
        const { router } = await import('@inertiajs/vue3');
        const wrapper = shallowMount(RecipeSearchModal, { props: defaultProps });

        await (wrapper.vm as any).clearSearch();

        expect(router.get).toHaveBeenCalledWith('mocked-route/recipes.index', expect.any(Object), expect.any(Object));
        const callArgs = vi.mocked(router.get).mock.calls[0][1] as Record<string, unknown>;
        expect(callArgs).not.toHaveProperty('search_term');
    });
});
