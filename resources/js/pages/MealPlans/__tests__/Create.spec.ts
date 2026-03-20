import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Create from '../Create.vue';

const mockFormPost = vi.fn();
const mockFormReset = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div></div>' },
    useForm: vi.fn((initial: any) => ({
        ...initial,
        errors: {},
        processing: false,
        post: mockFormPost,
        reset: mockFormReset,
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
        template: '<button :type="type || \'button\'" :disabled="disabled" @click="$attrs.onClick?.()"><slot /></button>',
        props: ['type', 'disabled'],
        inheritAttrs: false,
    },
}));

vi.mock('@/components/ui/input', () => ({
    Input: {
        name: 'Input',
        template: '<input :type="type || \'text\'" />',
        props: ['type', 'modelValue'],
    },
}));

vi.mock('@/components/ui/input-error', () => ({
    InputError: { name: 'InputError', template: '<span></span>' },
}));

vi.mock('@/components/ui/label', () => ({
    Label: { name: 'Label', template: '<label><slot /></label>' },
}));

vi.mock('lucide-vue-next', () => ({
    MinusIcon: { name: 'MinusIcon', template: '<svg></svg>' },
    PlusIcon: { name: 'PlusIcon', template: '<svg></svg>' },
}));

const routeMock = vi.fn((name: string) => `/mocked/${name}`);
vi.stubGlobal('route', routeMock);
const historyBackMock = vi.fn();
vi.stubGlobal('history', { back: historyBackMock });

const globalMocks = { global: { mocks: { route: routeMock } } };

describe('MealPlans/Create page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routeMock.mockImplementation((name: string) => `/mocked/${name}`);
    });

    it('renders the app layout', () => {
        const wrapper = mount(Create, globalMocks);
        expect(wrapper.find('[data-testid="app-layout"]').exists()).toBe(true);
    });

    it('renders the page title', () => {
        const wrapper = mount(Create, globalMocks);
        expect(wrapper.text()).toContain('Create Meal Plan');
    });

    it('renders a submit button', () => {
        const wrapper = mount(Create, globalMocks);
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('renders a cancel button', () => {
        const wrapper = mount(Create, globalMocks);
        expect(wrapper.text()).toContain('Cancel');
    });

    it('renders duration radio options', () => {
        const wrapper = mount(Create, globalMocks);
        const radioInputs = wrapper.findAll('input[type="radio"]');
        expect(radioInputs.length).toBe(2);
    });

    it('incrementPeople increases people_count', () => {
        const wrapper = mount(Create, globalMocks);
        const vm = wrapper.vm as any;
        vm.form.people_count = 2;
        vm.incrementPeople();
        expect(vm.form.people_count).toBe(3);
    });

    it('decrementPeople decreases people_count', () => {
        const wrapper = mount(Create, globalMocks);
        const vm = wrapper.vm as any;
        vm.form.people_count = 3;
        vm.decrementPeople();
        expect(vm.form.people_count).toBe(2);
    });

    it('incrementPeople does not exceed 20', () => {
        const wrapper = mount(Create, globalMocks);
        const vm = wrapper.vm as any;
        vm.form.people_count = 20;
        vm.incrementPeople();
        expect(vm.form.people_count).toBe(20);
    });

    it('decrementPeople does not go below 1', () => {
        const wrapper = mount(Create, globalMocks);
        const vm = wrapper.vm as any;
        vm.form.people_count = 1;
        vm.decrementPeople();
        expect(vm.form.people_count).toBe(1);
    });

    it('submit calls form.post with meal-plans.store route', () => {
        const wrapper = mount(Create, globalMocks);
        (wrapper.vm as any).submit();
        expect(mockFormPost).toHaveBeenCalledWith('/mocked/meal-plans.store', expect.any(Object));
    });

    it('cancel calls window.history.back', () => {
        const wrapper = mount(Create, globalMocks);
        (wrapper.vm as any).cancel();
        expect(historyBackMock).toHaveBeenCalled();
    });
});
