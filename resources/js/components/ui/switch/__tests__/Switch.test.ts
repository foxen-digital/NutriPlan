import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import Switch from '../switch.vue';

vi.mock('@/lib/utils', () => ({
    cn: (...inputs: any[]) => inputs.filter(Boolean).join(' '),
}));

const SwitchRootStub = {
    name: 'SwitchRoot',
    props: ['checked'],
    template: '<button role="switch" :aria-checked="checked" :class="$attrs.class" @click="$emit(\'update:checked\', !checked)"><slot /></button>',
    inheritAttrs: false,
    emits: ['update:checked'],
};

const SwitchThumbStub = {
    name: 'SwitchThumb',
    template: '<span class="switch-thumb"></span>',
};

describe('Switch', () => {
    const stubs = { SwitchRoot: SwitchRootStub, SwitchThumb: SwitchThumbStub };

    it('renders correctly', () => {
        const wrapper = mount(Switch, {
            props: { checked: false },
            global: { stubs },
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('passes checked prop to SwitchRoot', () => {
        const wrapper = mount(Switch, {
            props: { checked: true },
            global: { stubs },
        });
        const root = wrapper.find('[role="switch"]');
        expect(root.attributes('aria-checked')).toBe('true');
    });

    it('renders unchecked state correctly', () => {
        const wrapper = mount(Switch, {
            props: { checked: false },
            global: { stubs },
        });
        const root = wrapper.find('[role="switch"]');
        expect(root.attributes('aria-checked')).toBe('false');
    });

    it('emits update:checked when toggled', async () => {
        const wrapper = mount(Switch, {
            props: { checked: false },
            global: { stubs },
        });

        await wrapper.find('[role="switch"]').trigger('click');
        expect(wrapper.emitted('update:checked')).toBeTruthy();
        expect(wrapper.emitted('update:checked')![0]).toEqual([true]);
    });

    it('applies custom className', () => {
        const wrapper = mount(Switch, {
            props: { checked: false, className: 'my-switch' },
            global: { stubs },
        });
        expect(wrapper.find('[role="switch"]').attributes('class')).toContain('my-switch');
    });
});
