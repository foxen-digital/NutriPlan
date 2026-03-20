import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Register from '../Register.vue';

const mockFormPost = vi.fn();
const mockFormReset = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div></div>' },
    useForm: vi.fn(() => ({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        errors: {},
        processing: false,
        post: mockFormPost,
        reset: mockFormReset,
    })),
}));

vi.mock('@/layouts/AuthLayout.vue', () => ({
    default: {
        name: 'AuthLayout',
        template: '<div data-testid="auth-layout"><slot /></div>',
        props: ['title', 'description'],
    },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        name: 'Button',
        template: '<button :type="type || \'button\'" :disabled="disabled"><slot /></button>',
        props: ['type', 'disabled'],
    },
}));

vi.mock('@/components/ui/input', () => ({
    Input: {
        name: 'Input',
        template: '<input :type="type || \'text\'" />',
        props: ['type', 'modelValue'],
    },
}));

vi.mock('@/components/ui/label', () => ({
    Label: { name: 'Label', template: '<label><slot /></label>' },
}));

vi.mock('@/components/InputError.vue', () => ({
    default: { name: 'InputError', template: '<span></span>' },
}));

vi.mock('@/components/TextLink.vue', () => ({
    default: {
        name: 'TextLink',
        template: '<a :href="href"><slot /></a>',
        props: ['href'],
    },
}));

vi.mock('lucide-vue-next', () => ({
    LoaderCircle: { name: 'LoaderCircle', template: '<svg></svg>' },
}));

const routeMock = vi.fn((name: string) => `/mocked/${name}`);
vi.stubGlobal('route', routeMock);

describe('Register page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routeMock.mockImplementation((name: string) => `/mocked/${name}`);
    });

    it('renders the auth layout', () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.find('[data-testid="auth-layout"]').exists()).toBe(true);
    });

    it('renders a submit button', () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('renders Create account button text', () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).toContain('Create account');
    });

    it('renders log in link for existing users', () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).toContain('Log in');
    });

    it('renders four input fields (name, email, password, confirm)', () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.findAll('input').length).toBe(4);
    });

    it('submit calls form.post with the register route', async () => {
        const wrapper = mount(Register, {
            global: { mocks: { route: routeMock } },
        });
        await (wrapper.vm as any).submit();
        expect(mockFormPost).toHaveBeenCalledWith('/mocked/register', expect.any(Object));
    });
});
