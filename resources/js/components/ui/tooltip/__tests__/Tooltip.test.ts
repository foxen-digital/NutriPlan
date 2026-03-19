import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '..';

vi.mock('@/lib/utils', () => ({
    cn: (...inputs: any[]) => inputs.filter(Boolean).join(' '),
}));

vi.mock('radix-vue', () => ({
    TooltipRoot: { name: 'TooltipRoot', template: '<div data-testid="tooltip-root"><slot /></div>' },
    TooltipProvider: { name: 'TooltipProvider', template: '<div data-testid="tooltip-provider"><slot /></div>' },
    TooltipTrigger: { name: 'TooltipTrigger', template: '<div data-testid="tooltip-trigger"><slot /></div>' },
    TooltipContent: { name: 'TooltipContent', template: '<div data-testid="tooltip-content"><slot /></div>' },
    TooltipPortal: { name: 'TooltipPortal', template: '<div data-testid="tooltip-portal"><slot /></div>' },
    useForwardPropsEmits: () => ({}),
    useForwardProps: () => ({}),
}));

describe('Tooltip', () => {
    it('renders the tooltip root', () => {
        const wrapper = mount(Tooltip);
        expect(wrapper.find('[data-testid="tooltip-root"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(Tooltip, {
            slots: { default: '<span data-testid="slot">inner</span>' },
        });
        expect(wrapper.find('[data-testid="slot"]').exists()).toBe(true);
    });
});

describe('TooltipProvider', () => {
    it('renders the provider', () => {
        const wrapper = mount(TooltipProvider);
        expect(wrapper.find('[data-testid="tooltip-provider"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TooltipProvider, {
            slots: { default: '<span data-testid="child">child</span>' },
        });
        expect(wrapper.find('[data-testid="child"]').exists()).toBe(true);
    });
});

describe('TooltipTrigger', () => {
    it('renders the trigger', () => {
        const wrapper = mount(TooltipTrigger);
        expect(wrapper.find('[data-testid="tooltip-trigger"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TooltipTrigger, {
            slots: { default: '<button>Hover me</button>' },
        });
        expect(wrapper.find('button').exists()).toBe(true);
    });
});

describe('TooltipContent', () => {
    it('renders inside a portal', () => {
        const wrapper = mount(TooltipContent);
        expect(wrapper.find('[data-testid="tooltip-portal"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="tooltip-content"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TooltipContent, {
            slots: { default: 'Tooltip text' },
        });
        expect(wrapper.text()).toContain('Tooltip text');
    });

    it('applies custom class', () => {
        const wrapper = mount(TooltipContent, { props: { class: 'my-tooltip' } });
        const content = wrapper.find('[data-testid="tooltip-content"]');
        expect(content.attributes('class')).toContain('my-tooltip');
    });
});
