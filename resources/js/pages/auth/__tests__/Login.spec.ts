import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Login from '../Login.vue';

const mockFormPost = vi.fn();
const mockFormReset = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div></div>' },
    useForm: vi.fn(() => ({
        email: '',
        password: '',
        remember: false,
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

vi.mock('@/components/ui/checkbox', () => ({
    Checkbox: { name: 'Checkbox', template: '<input type="checkbox" />' },
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
// vi.stubGlobal for setup() method access; global.mocks for template access
vi.stubGlobal('route', routeMock);

describe('Login page', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        routeMock.mockImplementation((name: string) => `/mocked/${name}`);
    });

    it('renders the auth layout', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.find('[data-testid="auth-layout"]').exists()).toBe(true);
    });

    it('renders email and password inputs', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        const inputs = wrapper.findAll('input');
        const types = inputs.map((i) => i.attributes('type'));
        expect(types).toContain('email');
        expect(types).toContain('password');
    });

    it('renders a submit button', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it('renders a sign up link', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).toContain('Sign up');
    });

    it('shows forgot password link when canResetPassword is true', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).toContain('Forgot password?');
    });

    it('hides forgot password link when canResetPassword is false', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: false },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).not.toContain('Forgot password?');
    });

    it('shows status message when status prop is provided', () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true, status: 'Password reset sent!' },
            global: { mocks: { route: routeMock } },
        });
        expect(wrapper.text()).toContain('Password reset sent!');
    });

    it('submit calls form.post with the login route', async () => {
        const wrapper = mount(Login, {
            props: { canResetPassword: true },
            global: { mocks: { route: routeMock } },
        });
        await (wrapper.vm as any).submit();
        expect(mockFormPost).toHaveBeenCalledWith('/mocked/login', expect.any(Object));
    });
});
