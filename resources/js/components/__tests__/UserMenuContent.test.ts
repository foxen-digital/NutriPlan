import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        template: '<a :href="$attrs.href" :method="$attrs.method"><slot /></a>',
        inheritAttrs: false,
    },
}));

vi.mock('@/components/ui/dropdown-menu', () => ({
    DropdownMenuGroup: { name: 'DropdownMenuGroup', template: '<div><slot /></div>' },
    DropdownMenuItem: { name: 'DropdownMenuItem', template: '<div><slot /></div>' },
    DropdownMenuLabel: { name: 'DropdownMenuLabel', template: '<div><slot /></div>' },
    DropdownMenuSeparator: { name: 'DropdownMenuSeparator', template: '<hr class="dropdown-separator" />' },
}));

vi.mock('@/components/UserInfo.vue', () => ({
    default: { name: 'UserInfo', template: '<div data-testid="user-info"></div>' },
}));

vi.mock('lucide-vue-next', () => ({
    LogOut: { name: 'LogOut', template: '<svg></svg>' },
    Settings: { name: 'Settings', template: '<svg></svg>' },
}));

const routeMock = vi.fn((name: string) => `/mocked/${name}`);
vi.stubGlobal('route', routeMock);

import UserMenuContent from '../UserMenuContent.vue';

const mockUser = {
    id: 1,
    name: 'John Doe',
    email: 'john@example.com',
};

const mountOptions = {
    global: {
        mocks: { route: routeMock },
    },
};

describe('UserMenuContent', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders user info component', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        expect(wrapper.find('[data-testid="user-info"]').exists()).toBe(true);
    });

    it('renders settings link', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        expect(wrapper.text()).toContain('Settings');
    });

    it('renders log out link', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        expect(wrapper.text()).toContain('Log out');
    });

    it('settings link points to profile.edit route', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        const links = wrapper.findAll('a');
        const settingsLink = links.find((l) => l.text().includes('Settings'));
        expect(settingsLink?.attributes('href')).toBe('/mocked/profile.edit');
    });

    it('logout link uses POST method', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        const links = wrapper.findAll('a');
        const logoutLink = links.find((l) => l.text().includes('Log out'));
        expect(logoutLink?.attributes('method')).toBe('post');
    });

    it('renders separator elements', () => {
        const wrapper = mount(UserMenuContent, {
            props: { user: mockUser as any },
            ...mountOptions,
        });
        expect(wrapper.findAll('hr').length).toBeGreaterThan(0);
    });
});
