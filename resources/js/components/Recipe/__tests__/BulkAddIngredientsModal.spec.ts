import { shallowMount } from '@vue/test-utils';
import axios from 'axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import BulkAddIngredientsModal from '../BulkAddIngredientsModal.vue';

// Mock axios
vi.mock('axios');

// Mock route function
vi.stubGlobal(
    'route',
    vi.fn((name: string) => `mocked-route/${name}`),
);

describe('BulkAddIngredientsModal', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('posts trimmed, non-empty lines to the normalize endpoint', async () => {
        vi.mocked(axios.post).mockResolvedValueOnce({ data: { ingredients: [] } });

        const wrapper = shallowMount(BulkAddIngredientsModal, {
            props: {
                open: true,
            },
        });

        (wrapper.vm as any).rawIngredients = '  200 g plain flour \n\n2 red onions\n   \n';

        await (wrapper.vm as any).submit();

        expect(axios.post).toHaveBeenCalledWith('mocked-route/ingredients.normalize', {
            ingredients: ['200 g plain flour', '2 red onions'],
        });
    });

    it('emits ingredients-added and closes on success', async () => {
        const rows = [
            {
                ingredient_id: 42,
                name: 'plain flour',
                amount: 200,
                unit: 'g',
                description: 'plain flour',
                original_string: '200 g plain flour',
            },
        ];
        vi.mocked(axios.post).mockResolvedValueOnce({ data: { ingredients: rows } });

        const wrapper = shallowMount(BulkAddIngredientsModal, {
            props: {
                open: true,
            },
        });

        (wrapper.vm as any).rawIngredients = '200 g plain flour';

        await (wrapper.vm as any).submit();

        expect(wrapper.emitted('ingredients-added')).toBeTruthy();
        expect(wrapper.emitted('ingredients-added')![0]).toEqual([rows]);
        expect(wrapper.emitted('update:open')).toBeTruthy();
        expect(wrapper.emitted('update:open')![0]).toEqual([false]);
        expect((wrapper.vm as any).rawIngredients).toBe('');
    });

    it('keeps the modal open and shows an error when the request fails', async () => {
        vi.mocked(axios.post).mockRejectedValueOnce(new Error('API error'));
        console.error = vi.fn(); // Mock console.error

        const wrapper = shallowMount(BulkAddIngredientsModal, {
            props: {
                open: true,
            },
        });

        (wrapper.vm as any).rawIngredients = '200 g plain flour';

        await (wrapper.vm as any).submit();

        expect((wrapper.vm as any).error).toBe('Could not process the ingredients. Please try again.');
        expect((wrapper.vm as any).rawIngredients).toBe('200 g plain flour');
        expect(wrapper.emitted('ingredients-added')).toBeFalsy();
        expect(wrapper.emitted('update:open')).toBeFalsy();
        expect(console.error).toHaveBeenCalledWith('Error normalizing ingredients:', expect.any(Error));
    });

    it('does not call the API when no lines are entered', async () => {
        const wrapper = shallowMount(BulkAddIngredientsModal, {
            props: {
                open: true,
            },
        });

        (wrapper.vm as any).rawIngredients = '   \n   ';

        await (wrapper.vm as any).submit();

        expect(axios.post).not.toHaveBeenCalled();
        expect(wrapper.emitted('ingredients-added')).toBeFalsy();
        expect(wrapper.emitted('update:open')).toBeFalsy();
    });
});
