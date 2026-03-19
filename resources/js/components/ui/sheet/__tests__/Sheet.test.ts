import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import { Sheet, SheetClose, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '..';

vi.mock('@/lib/utils', () => ({
    cn: (...inputs: any[]) => inputs.filter(Boolean).join(' '),
}));

vi.mock('radix-vue', () => ({
    DialogRoot: { name: 'DialogRoot', template: '<div data-testid="sheet-root"><slot /></div>' },
    DialogTrigger: { name: 'DialogTrigger', template: '<button data-testid="sheet-trigger"><slot /></button>' },
    DialogPortal: { name: 'DialogPortal', template: '<div data-testid="sheet-portal"><slot /></div>' },
    DialogOverlay: { name: 'DialogOverlay', template: '<div data-testid="sheet-overlay"><slot /></div>' },
    DialogContent: { name: 'DialogContent', template: '<div data-testid="sheet-content"><slot /></div>' },
    DialogClose: { name: 'DialogClose', template: '<button data-testid="sheet-close"><slot /></button>' },
    DialogTitle: { name: 'DialogTitle', template: '<h2 data-testid="sheet-title"><slot /></h2>' },
    DialogDescription: { name: 'DialogDescription', template: '<p data-testid="sheet-description"><slot /></p>' },
    useForwardPropsEmits: () => ({}),
    useForwardProps: () => ({}),
}));

vi.mock('lucide-vue-next', () => ({
    X: { name: 'X', template: '<div data-testid="x-icon"></div>' },
}));

describe('Sheet', () => {
    it('renders the root element', () => {
        const wrapper = mount(Sheet);
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.find('[data-testid="sheet-root"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(Sheet, {
            slots: { default: '<span data-testid="slot-content">content</span>' },
        });
        expect(wrapper.find('[data-testid="slot-content"]').exists()).toBe(true);
    });
});

describe('SheetTrigger', () => {
    it('renders the trigger element', () => {
        const wrapper = mount(SheetTrigger);
        expect(wrapper.find('[data-testid="sheet-trigger"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetTrigger, {
            slots: { default: 'Open Sheet' },
        });
        expect(wrapper.text()).toBe('Open Sheet');
    });
});

describe('SheetContent', () => {
    it('renders portal, overlay and content', () => {
        const wrapper = mount(SheetContent);
        expect(wrapper.find('[data-testid="sheet-portal"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="sheet-overlay"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="sheet-content"]').exists()).toBe(true);
    });

    it('includes a close button with X icon', () => {
        const wrapper = mount(SheetContent);
        expect(wrapper.find('[data-testid="sheet-close"]').exists()).toBe(true);
        expect(wrapper.find('[data-testid="x-icon"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetContent, {
            slots: { default: '<p data-testid="inner">body</p>' },
        });
        expect(wrapper.find('[data-testid="inner"]').exists()).toBe(true);
    });
});

describe('SheetClose', () => {
    it('renders a close button', () => {
        const wrapper = mount(SheetClose);
        expect(wrapper.find('[data-testid="sheet-close"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetClose, {
            slots: { default: 'Close' },
        });
        expect(wrapper.text()).toBe('Close');
    });
});

describe('SheetHeader', () => {
    it('renders with default classes', () => {
        const wrapper = mount(SheetHeader);
        expect(wrapper.exists()).toBe(true);
        expect(wrapper.attributes('class')).toContain('flex flex-col gap-y-2');
    });

    it('applies custom class', () => {
        const wrapper = mount(SheetHeader, { props: { class: 'custom-header' } });
        expect(wrapper.attributes('class')).toContain('custom-header');
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetHeader, {
            slots: { default: '<span data-testid="header-slot">title</span>' },
        });
        expect(wrapper.find('[data-testid="header-slot"]').exists()).toBe(true);
    });
});

describe('SheetFooter', () => {
    it('renders correctly', () => {
        const wrapper = mount(SheetFooter);
        expect(wrapper.exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetFooter, {
            slots: { default: '<button>Submit</button>' },
        });
        expect(wrapper.find('button').exists()).toBe(true);
    });
});

describe('SheetTitle', () => {
    it('renders a title element', () => {
        const wrapper = mount(SheetTitle);
        expect(wrapper.find('[data-testid="sheet-title"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetTitle, {
            slots: { default: 'Sheet Title' },
        });
        expect(wrapper.text()).toBe('Sheet Title');
    });
});

describe('SheetDescription', () => {
    it('renders a description element', () => {
        const wrapper = mount(SheetDescription);
        expect(wrapper.find('[data-testid="sheet-description"]').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(SheetDescription, {
            slots: { default: 'Sheet Description' },
        });
        expect(wrapper.text()).toBe('Sheet Description');
    });
});
