import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import RegistrationClosed from '../RegistrationClosed.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<div></div>' },
}));

vi.mock('@/layouts/AuthLayout.vue', () => ({
    default: {
        name: 'AuthLayout',
        template: '<div data-testid="auth-layout"><slot /></div>',
        props: ['title', 'description'],
    },
}));

vi.mock('@/components/TextLink.vue', () => ({
    default: {
        name: 'TextLink',
        template: '<a :href="href"><slot /></a>',
        props: ['href'],
    },
}));

const routeMock = vi.fn((name: string) => `/mocked/${name}`);

describe('RegistrationClosed page', () => {
    const mountPage = () => mount(RegistrationClosed, { global: { mocks: { route: routeMock } } });

    it('renders the auth layout', () => {
        const wrapper = mountPage();
        expect(wrapper.find('[data-testid="auth-layout"]').exists()).toBe(true);
    });

    it('renders the closed message', () => {
        const wrapper = mountPage();
        expect(wrapper.text()).toContain('private access');
    });

    it('renders a back to login link', () => {
        const wrapper = mountPage();
        expect(wrapper.text()).toContain('Back to login');
    });

    it('back to login link targets the login route', () => {
        const wrapper = mountPage();
        const link = wrapper.find('a');
        expect(link.attributes('href')).toBe('/mocked/login');
        expect(routeMock).toHaveBeenCalledWith('login');
    });
});
