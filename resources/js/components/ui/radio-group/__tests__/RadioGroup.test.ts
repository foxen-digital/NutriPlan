import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import { RadioGroup, RadioGroupItem } from '..';

vi.mock('@/lib/utils', () => ({
    cn: (...inputs: any[]) => inputs.filter(Boolean).join(' '),
}));

vi.mock('radix-vue', () => ({
    RadioGroupRoot: {
        name: 'RadioGroupRoot',
        template: '<div data-testid="radio-group-root" :class="$attrs.class"><slot /></div>',
        inheritAttrs: false,
    },
    RadioGroupItem: {
        name: 'RadioGroupItem',
        template: '<button role="radio" :class="$attrs.class"><slot /></button>',
        inheritAttrs: false,
    },
    RadioGroupIndicator: {
        name: 'RadioGroupIndicator',
        template: '<span data-testid="radio-indicator"><slot /></span>',
    },
    useForwardPropsEmits: () => ({}),
    useForwardProps: () => ({}),
}));

vi.mock('lucide-vue-next', () => ({
    Circle: { name: 'Circle', template: '<svg data-testid="circle-icon"></svg>' },
}));

describe('RadioGroup', () => {
    it('renders the root element', () => {
        const wrapper = mount(RadioGroup);
        expect(wrapper.find('[data-testid="radio-group-root"]').exists()).toBe(true);
    });

    it('applies default grid class', () => {
        const wrapper = mount(RadioGroup);
        expect(wrapper.find('[data-testid="radio-group-root"]').attributes('class')).toContain('grid gap-2');
    });

    it('applies custom class', () => {
        const wrapper = mount(RadioGroup, { props: { class: 'flex space-x-4' } });
        expect(wrapper.find('[data-testid="radio-group-root"]').attributes('class')).toContain('flex space-x-4');
    });

    it('renders slot content', () => {
        const wrapper = mount(RadioGroup, {
            slots: { default: '<span data-testid="radio-item">Option</span>' },
        });
        expect(wrapper.find('[data-testid="radio-item"]').exists()).toBe(true);
    });
});

describe('RadioGroupItem', () => {
    it('renders a radio button', () => {
        const wrapper = mount(RadioGroupItem, {
            props: { value: 'option-1' },
        });
        expect(wrapper.find('[role="radio"]').exists()).toBe(true);
    });

    it('applies default styling classes', () => {
        const wrapper = mount(RadioGroupItem, {
            props: { value: 'option-1' },
        });
        const radio = wrapper.find('[role="radio"]');
        expect(radio.attributes('class')).toContain('rounded-full');
        expect(radio.attributes('class')).toContain('border');
    });

    it('includes circle indicator icon', () => {
        const wrapper = mount(RadioGroupItem, {
            props: { value: 'option-1' },
        });
        expect(wrapper.find('[data-testid="circle-icon"]').exists()).toBe(true);
    });

    it('applies custom class', () => {
        const wrapper = mount(RadioGroupItem, {
            props: { value: 'option-1', class: 'custom-radio' },
        });
        expect(wrapper.find('[role="radio"]').attributes('class')).toContain('custom-radio');
    });
});
