import NavMain from '../NavMain.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    Link: {
        name: 'Link',
        template: '<a :href="href"><slot /></a>',
        props: ['href'],
    },
    usePage: vi.fn(() => ({
        url: '/recipes',
        props: {},
    })),
}));

vi.mock('@/components/ui/sidebar', () => ({
    SidebarGroup: { name: 'SidebarGroup', template: '<div data-testid="sidebar-group"><slot /></div>' },
    SidebarGroupLabel: { name: 'SidebarGroupLabel', template: '<div data-testid="sidebar-group-label"><slot /></div>' },
    SidebarMenu: { name: 'SidebarMenu', template: '<ul data-testid="sidebar-menu"><slot /></ul>' },
    SidebarMenuButton: {
        name: 'SidebarMenuButton',
        template: '<button :data-active="isActive ? \'true\' : \'false\'"><slot /></button>',
        props: ['isActive', 'asChild', 'tooltip'],
    },
    SidebarMenuItem: { name: 'SidebarMenuItem', template: '<li data-testid="sidebar-menu-item"><slot /></li>' },
}));

const mockNavItems = [
    { title: 'Recipes', href: '/recipes', icon: { name: 'RecipesIcon', template: '<svg></svg>' } },
    { title: 'Meal Plans', href: '/meal-plans', icon: { name: 'MealPlansIcon', template: '<svg></svg>' } },
];

describe('NavMain', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders the correct number of nav items', () => {
        const wrapper = mount(NavMain, { props: { items: mockNavItems } });
        expect(wrapper.findAll('[data-testid="sidebar-menu-item"]')).toHaveLength(2);
    });

    it('renders nav item titles', () => {
        const wrapper = mount(NavMain, { props: { items: mockNavItems } });
        expect(wrapper.text()).toContain('Recipes');
        expect(wrapper.text()).toContain('Meal Plans');
    });

    it('marks active item based on current page url', () => {
        const wrapper = mount(NavMain, { props: { items: mockNavItems } });
        const buttons = wrapper.findAll('button');
        // /recipes matches current URL '/recipes' from usePage mock
        expect(buttons[0].attributes('data-active')).toBe('true');
        expect(buttons[1].attributes('data-active')).toBe('false');
    });

    it('renders Platform label', () => {
        const wrapper = mount(NavMain, { props: { items: mockNavItems } });
        expect(wrapper.text()).toContain('Platform');
    });

    it('renders with empty items array', () => {
        const wrapper = mount(NavMain, { props: { items: [] } });
        expect(wrapper.findAll('[data-testid="sidebar-menu-item"]')).toHaveLength(0);
    });
});
