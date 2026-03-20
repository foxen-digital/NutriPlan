import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Index from '../Index.vue';

const mockFormDelete = vi.fn();
const mockFormPost = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div></div>' },
    Link: {
        name: 'Link',
        template: '<a :href="href"><slot /></a>',
        props: ['href'],
    },
    useForm: vi.fn((initial: any) => ({
        ...initial,
        errors: {},
        processing: false,
        delete: mockFormDelete,
        post: mockFormPost,
    })),
}));

vi.mock('@/layouts/AppLayout.vue', () => ({
    default: {
        name: 'AppLayout',
        template: '<div data-testid="app-layout"><slot /></div>',
    },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        name: 'Button',
        template: '<button @click="$attrs.onClick?.()"><slot /></button>',
        inheritAttrs: false,
    },
}));

vi.mock('@/components/ui/badge', () => ({
    Badge: { name: 'Badge', template: '<span class="badge"><slot /></span>' },
}));

vi.mock('@/components/ui/dialog', () => ({
    Dialog: { name: 'Dialog', template: '<div><slot /></div>', props: ['open'] },
    DialogContent: { name: 'DialogContent', template: '<div><slot /></div>' },
    DialogHeader: { name: 'DialogHeader', template: '<div><slot /></div>' },
    DialogTitle: { name: 'DialogTitle', template: '<div><slot /></div>' },
    DialogDescription: { name: 'DialogDescription', template: '<div><slot /></div>' },
    DialogFooter: { name: 'DialogFooter', template: '<div><slot /></div>' },
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

vi.mock('@/components/ui/input', () => ({
    Input: { name: 'Input', template: '<input />' },
}));

vi.mock('@/components/ui/input-error', () => ({
    InputError: { name: 'InputError', template: '<span></span>' },
}));

vi.mock('@/components/ui/label', () => ({
    Label: { name: 'Label', template: '<label><slot /></label>' },
}));

vi.mock('lucide-vue-next', () => ({
    CalendarIcon: { name: 'CalendarIcon', template: '<svg></svg>' },
    CopyIcon: { name: 'CopyIcon', template: '<svg></svg>' },
    EllipsisVerticalIcon: { name: 'EllipsisVerticalIcon', template: '<svg></svg>' },
    EyeIcon: { name: 'EyeIcon', template: '<svg></svg>' },
    MinusIcon: { name: 'MinusIcon', template: '<svg></svg>' },
    PlusIcon: { name: 'PlusIcon', template: '<svg></svg>' },
    TrashIcon: { name: 'TrashIcon', template: '<svg></svg>' },
}));

const routeMock = vi.fn((name: string, param?: any) => `/mocked/${name}/${param ?? ''}`);
vi.stubGlobal('route', routeMock);

const mockMealPlans = [
    {
        id: 1,
        name: 'Weekly Plan',
        start_date: '2026-03-01',
        end_date: '2026-03-07',
        duration: 7,
        people_count: 2,
    },
    {
        id: 2,
        name: 'Family Plan',
        start_date: '2026-04-01',
        end_date: '2026-04-14',
        duration: 14,
        people_count: 4,
    },
];

const globalMocks = { global: { mocks: { route: routeMock } } };

describe('MealPlans/Index page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routeMock.mockImplementation((name: string, param?: any) => `/mocked/${name}/${param ?? ''}`);
    });

    it('renders the app layout', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        expect(wrapper.find('[data-testid="app-layout"]').exists()).toBe(true);
    });

    it('renders meal plan names', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        expect(wrapper.text()).toContain('Weekly Plan');
        expect(wrapper.text()).toContain('Family Plan');
    });

    it('renders people count badges', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        expect(wrapper.text()).toContain('2 people');
        expect(wrapper.text()).toContain('4 people');
    });

    it('shows empty state when no meal plans', () => {
        const wrapper = mount(Index, { props: { mealPlans: [] }, ...globalMocks });
        expect(wrapper.text()).toContain('No meal plans');
    });

    it('confirmDeleteMealPlan opens delete modal', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        (wrapper.vm as any).confirmDeleteMealPlan(mockMealPlans[0]);
        expect((wrapper.vm as any).isDeleteModalOpen).toBe(true);
        expect((wrapper.vm as any).mealPlanToDelete).toEqual(mockMealPlans[0]);
    });

    it('showCopyModal opens copy modal with meal plan', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        (wrapper.vm as any).showCopyModal(mockMealPlans[0]);
        expect((wrapper.vm as any).isCopyModalOpen).toBe(true);
        expect((wrapper.vm as any).mealPlanToCopy).toEqual(mockMealPlans[0]);
    });

    it('deleteMealPlan calls form.delete when mealPlanToDelete is set', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        (wrapper.vm as any).mealPlanToDelete = mockMealPlans[0];
        (wrapper.vm as any).deleteMealPlan();
        expect(mockFormDelete).toHaveBeenCalledWith('/mocked/meal-plans.destroy/1', expect.any(Object));
    });

    it('incrementPeople increases copyForm.people_count', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        const vm = wrapper.vm as any;
        vm.copyForm.people_count = 3;
        vm.incrementPeople();
        expect(vm.copyForm.people_count).toBe(4);
    });

    it('decrementPeople decreases copyForm.people_count', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        const vm = wrapper.vm as any;
        vm.copyForm.people_count = 3;
        vm.decrementPeople();
        expect(vm.copyForm.people_count).toBe(2);
    });

    it('incrementPeople does not exceed 20', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        const vm = wrapper.vm as any;
        vm.copyForm.people_count = 20;
        vm.incrementPeople();
        expect(vm.copyForm.people_count).toBe(20);
    });

    it('decrementPeople does not go below 1', () => {
        const wrapper = mount(Index, { props: { mealPlans: mockMealPlans }, ...globalMocks });
        const vm = wrapper.vm as any;
        vm.copyForm.people_count = 1;
        vm.decrementPeople();
        expect(vm.copyForm.people_count).toBe(1);
    });

    it('isPastMealPlan returns true for plans before today', () => {
        const wrapper = mount(Index, { props: { mealPlans: [] }, ...globalMocks });
        const pastPlan = { ...mockMealPlans[0], start_date: '2020-01-01', duration: 7 };
        expect((wrapper.vm as any).isPastMealPlan(pastPlan)).toBe(true);
    });

    it('isPastMealPlan returns false for future plans', () => {
        const wrapper = mount(Index, { props: { mealPlans: [] }, ...globalMocks });
        const futurePlan = { ...mockMealPlans[0], start_date: '2030-01-01', duration: 7 };
        expect((wrapper.vm as any).isPastMealPlan(futurePlan)).toBe(false);
    });
});
