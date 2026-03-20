import { mount } from '@vue/test-utils';
import axios from 'axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import MealAssignmentCard from '../MealAssignmentCard.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', template: '<a :href="href"><slot /></a>', props: ['href'] },
}));

vi.mock('@/components/ui/button', () => ({
    Button: { name: 'Button', template: '<button><slot /></button>' },
}));

vi.mock('@/components/ui/dropdown-menu', () => ({
    DropdownMenu: { name: 'DropdownMenu', template: '<div><slot /></div>' },
    DropdownMenuContent: { name: 'DropdownMenuContent', template: '<div><slot /></div>' },
    DropdownMenuItem: {
        name: 'DropdownMenuItem',
        template: '<div @click="$attrs.onClick?.()"><slot /></div>',
        inheritAttrs: false,
    },
    DropdownMenuTrigger: { name: 'DropdownMenuTrigger', template: '<div><slot /></div>' },
}));

vi.mock('lucide-vue-next', () => ({
    EllipsisVerticalIcon: { name: 'EllipsisVerticalIcon', template: '<svg></svg>' },
    PencilIcon: { name: 'PencilIcon', template: '<svg></svg>' },
    TrashIcon: { name: 'TrashIcon', template: '<svg></svg>' },
    ChefHatIcon: { name: 'ChefHatIcon', template: '<svg></svg>' },
}));

vi.mock('axios');

// route() is called both in the template AND in setup() methods.
// global.mocks handles template access; vi.stubGlobal handles setup() method access.
const routeMock = vi.fn((name: string, param?: any) => `/mocked/${name}/${param ?? ''}`);
vi.stubGlobal('route', routeMock);

const mockAssignment = {
    id: 42,
    to_cook: false,
    servings: 2,
    meal_plan_recipe: {
        recipe: {
            title: 'Grilled Salmon',
            slug: 'grilled-salmon',
        },
    },
} as any;

const globalMocks = {
    global: {
        mocks: { route: routeMock },
    },
};

describe('MealAssignmentCard', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        // Reset the route mock after clearAllMocks restores it
        routeMock.mockImplementation((name: string, param?: any) => `/mocked/${name}/${param ?? ''}`);
    });

    it('renders the recipe title', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });
        expect(wrapper.text()).toContain('Grilled Salmon');
    });

    it('renders the assignment id', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });
        expect(wrapper.text()).toContain('42');
    });

    it('applies amber styles when to_cook is true', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: { ...mockAssignment, to_cook: true } },
            ...globalMocks,
        });
        const outerDiv = wrapper.find('div');
        expect(outerDiv.attributes('class')).toContain('border-amber-400');
    });

    it('applies default styles when to_cook is false', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: { ...mockAssignment, to_cook: false } },
            ...globalMocks,
        });
        const outerDiv = wrapper.find('div');
        expect(outerDiv.attributes('class')).toContain('border-gray-200');
    });

    it('emits edit event when triggered', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });
        wrapper.vm.$emit('edit', mockAssignment);
        expect(wrapper.emitted('edit')).toBeTruthy();
    });

    it('emits remove event when triggered', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });
        wrapper.vm.$emit('remove', mockAssignment);
        expect(wrapper.emitted('remove')).toBeTruthy();
    });

    it('toggleToCook calls axios.post with assignment id', async () => {
        vi.mocked(axios.post).mockResolvedValueOnce({
            data: { success: true, to_cook: true },
        });

        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });

        await (wrapper.vm as any).toggleToCook();

        expect(axios.post).toHaveBeenCalled();
    });

    it('toggleToCook emits toggled event on success', async () => {
        vi.mocked(axios.post).mockResolvedValueOnce({
            data: { success: true, to_cook: true },
        });

        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });

        await (wrapper.vm as any).toggleToCook();

        expect(wrapper.emitted('toggled')).toBeTruthy();
        expect(wrapper.emitted('toggled')![0][0]).toMatchObject({ to_cook: true });
    });

    it('toggleToCook does not emit when API call fails', async () => {
        vi.mocked(axios.post).mockRejectedValueOnce(new Error('Network error'));
        console.error = vi.fn();

        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });

        await (wrapper.vm as any).toggleToCook();

        expect(wrapper.emitted('toggled')).toBeFalsy();
    });

    it('formatServings returns servings as string', () => {
        const wrapper = mount(MealAssignmentCard, {
            props: { assignment: mockAssignment },
            ...globalMocks,
        });
        expect((wrapper.vm as any).formatServings(3)).toBe('3');
    });
});
