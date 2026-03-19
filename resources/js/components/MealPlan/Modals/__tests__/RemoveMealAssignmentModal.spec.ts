import { shallowMount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import RemoveMealAssignmentModal from '../RemoveMealAssignmentModal.vue';

vi.mock('@/components/ui/dialog', () => ({
    Dialog: { template: '<div><slot /></div>' },
    DialogContent: { template: '<div><slot /></div>' },
    DialogHeader: { template: '<div><slot /></div>' },
    DialogTitle: { template: '<div><slot /></div>' },
    DialogDescription: { template: '<div><slot /></div>' },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        template: '<button @click="$attrs.onClick?.()"><slot /></button>',
        inheritAttrs: false,
    },
}));

const mockAssignment = {
    id: 1,
    to_cook: false,
    servings: 4,
    meal_plan_recipe: {
        recipe: {
            title: 'Pasta Carbonara',
            slug: 'pasta-carbonara',
        },
    },
} as any;

describe('RemoveMealAssignmentModal', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders when open is true', () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: mockAssignment },
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('renders when open is false', () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: false, assignment: mockAssignment },
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('emits update:open with false when closeModal is called', async () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: mockAssignment },
        });

        await (wrapper.vm as any).closeModal();

        expect(wrapper.emitted('update:open')).toBeTruthy();
        expect(wrapper.emitted('update:open')![0]).toEqual([false]);
    });

    it('emits confirm when confirmRemoval is called with valid assignment', async () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: mockAssignment },
        });

        await (wrapper.vm as any).confirmRemoval();

        expect(wrapper.emitted('confirm')).toBeTruthy();
    });

    it('does not emit confirm when assignment is null', async () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: null },
        });

        await (wrapper.vm as any).confirmRemoval();

        expect(wrapper.emitted('confirm')).toBeFalsy();
    });

    it('getRecipeTitle returns recipe title from assignment', () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: mockAssignment },
        });

        expect((wrapper.vm as any).getRecipeTitle(mockAssignment)).toBe('Pasta Carbonara');
    });

    it('getRecipeTitle returns fallback text for null assignment', () => {
        const wrapper = shallowMount(RemoveMealAssignmentModal, {
            props: { open: true, assignment: null },
        });

        expect((wrapper.vm as any).getRecipeTitle(null)).toBe('this meal');
    });
});
