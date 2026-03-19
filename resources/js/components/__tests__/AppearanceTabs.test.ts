import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const mockUpdateAppearance = vi.fn();
const mockAppearance = { value: 'system' as 'light' | 'dark' | 'system' };

vi.mock('@/composables/useAppearance', () => ({
    useAppearance: () => ({
        appearance: mockAppearance,
        updateAppearance: mockUpdateAppearance,
    }),
}));

vi.mock('lucide-vue-next', () => ({
    Sun: { template: '<svg data-testid="sun-icon"></svg>' },
    Moon: { template: '<svg data-testid="moon-icon"></svg>' },
    Monitor: { template: '<svg data-testid="monitor-icon"></svg>' },
}));

import AppearanceTabs from '../AppearanceTabs.vue';

describe('AppearanceTabs', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        mockAppearance.value = 'system';
    });

    it('renders three tab buttons', () => {
        const wrapper = mount(AppearanceTabs);
        expect(wrapper.findAll('button')).toHaveLength(3);
    });

    it('renders Light, Dark, and System labels', () => {
        const wrapper = mount(AppearanceTabs);
        expect(wrapper.text()).toContain('Light');
        expect(wrapper.text()).toContain('Dark');
        expect(wrapper.text()).toContain('System');
    });

    it('renders all theme icons', () => {
        const wrapper = mount(AppearanceTabs);
        expect(wrapper.find('[data-testid="sun-icon"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="moon-icon"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="monitor-icon"]').exists()).toBe(true);
    });

    it('calls updateAppearance with "light" when Light button is clicked', async () => {
        const wrapper = mount(AppearanceTabs);
        const buttons = wrapper.findAll('button');
        await buttons[0].trigger('click');
        expect(mockUpdateAppearance).toHaveBeenCalledWith('light');
    });

    it('calls updateAppearance with "dark" when Dark button is clicked', async () => {
        const wrapper = mount(AppearanceTabs);
        const buttons = wrapper.findAll('button');
        await buttons[1].trigger('click');
        expect(mockUpdateAppearance).toHaveBeenCalledWith('dark');
    });

    it('calls updateAppearance with "system" when System button is clicked', async () => {
        const wrapper = mount(AppearanceTabs);
        const buttons = wrapper.findAll('button');
        await buttons[2].trigger('click');
        expect(mockUpdateAppearance).toHaveBeenCalledWith('system');
    });

    it('applies custom class to container', () => {
        const wrapper = mount(AppearanceTabs, { props: { class: 'extra-class' } });
        expect(wrapper.attributes('class')).toContain('extra-class');
    });
});
