import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const mockFormDelete = vi.fn();
const mockFormReset = vi.fn();
const mockFormClearErrors = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    useForm: vi.fn(() => ({
        password: '',
        errors: { password: '' },
        processing: false,
        delete: mockFormDelete,
        reset: mockFormReset,
        clearErrors: mockFormClearErrors,
    })),
}));

vi.mock('@/components/ui/dialog', () => ({
    Dialog: { template: '<div><slot /></div>' },
    DialogContent: { template: '<div><slot /></div>' },
    DialogHeader: { template: '<div class="space-y-3"><slot /></div>' },
    DialogTitle: { template: '<div><slot /></div>' },
    DialogDescription: { template: '<div><slot /></div>' },
    DialogFooter: { template: '<div><slot /></div>' },
    DialogTrigger: { template: '<div><slot /></div>' },
    DialogClose: { template: '<div><slot /></div>' },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        template: '<button :disabled="$attrs.disabled" @click="$attrs.onClick?.()"><slot /></button>',
        inheritAttrs: false,
    },
}));

vi.mock('@/components/ui/input', () => ({
    Input: { template: '<input />' },
}));

vi.mock('@/components/ui/label', () => ({
    Label: { template: '<label><slot /></label>' },
}));

vi.mock('@/components/InputError.vue', () => ({
    default: { template: '<span></span>' },
}));

vi.mock('@/components/HeadingSmall.vue', () => ({
    default: { template: '<div data-testid="heading-small"></div>' },
}));

const routeMock = vi.fn((name: string) => `/mocked/${name}`);
vi.stubGlobal('route', routeMock);

import DeleteUser from '../DeleteUser.vue';

describe('DeleteUser', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders the component', () => {
        const wrapper = mount(DeleteUser);
        expect(wrapper.exists()).toBe(true);
    });

    it('renders the heading', () => {
        const wrapper = mount(DeleteUser);
        expect(wrapper.find('[data-testid="heading-small"]').exists()).toBe(true);
    });

    it('renders the delete account button', () => {
        const wrapper = mount(DeleteUser);
        expect(wrapper.text()).toContain('Delete account');
    });

    it('renders a warning section', () => {
        const wrapper = mount(DeleteUser);
        expect(wrapper.text()).toContain('Warning');
    });

    it('closeModal calls form.clearErrors and form.reset', () => {
        const wrapper = mount(DeleteUser);
        (wrapper.vm as any).closeModal();
        expect(mockFormClearErrors).toHaveBeenCalled();
        expect(mockFormReset).toHaveBeenCalled();
    });

    it('deleteUser calls form.delete with profile.destroy route', () => {
        const wrapper = mount(DeleteUser);
        const event = { preventDefault: vi.fn() } as any;
        (wrapper.vm as any).deleteUser(event);
        expect(mockFormDelete).toHaveBeenCalledWith('/mocked/profile.destroy', expect.any(Object));
    });
});
