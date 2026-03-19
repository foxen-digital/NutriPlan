import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '..';

vi.mock('@/lib/utils', () => ({
    cn: (...inputs: any[]) => inputs.filter(Boolean).join(' '),
}));

describe('Table', () => {
    it('renders a table element', () => {
        const wrapper = mount(Table);
        expect(wrapper.find('table').exists()).toBe(true);
    });

    it('wraps table in overflow container', () => {
        const wrapper = mount(Table);
        expect(wrapper.find('div').exists()).toBe(true);
        expect(wrapper.attributes('class')).toContain('w-full overflow-auto');
    });

    it('renders slot content', () => {
        const wrapper = mount(Table, {
            slots: { default: '<tbody data-testid="body"></tbody>' },
        });
        expect(wrapper.find('[data-testid="body"]').exists()).toBe(true);
    });

    it('applies custom class to the table element', () => {
        const wrapper = mount(Table, { attrs: { class: 'custom-table' } });
        expect(wrapper.find('table').attributes('class')).toContain('custom-table');
    });
});

describe('TableHeader', () => {
    it('renders a thead element', () => {
        const wrapper = mount(TableHeader);
        expect(wrapper.find('thead').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TableHeader, {
            slots: { default: '<tr data-testid="header-row"></tr>' },
        });
        expect(wrapper.find('[data-testid="header-row"]').exists()).toBe(true);
    });
});

describe('TableBody', () => {
    it('renders a tbody element', () => {
        const wrapper = mount(TableBody);
        expect(wrapper.find('tbody').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TableBody, {
            slots: { default: '<tr data-testid="body-row"></tr>' },
        });
        expect(wrapper.find('[data-testid="body-row"]').exists()).toBe(true);
    });
});

describe('TableRow', () => {
    it('renders a tr element', () => {
        const wrapper = mount(TableRow);
        expect(wrapper.find('tr').exists()).toBe(true);
    });

    it('includes transition and hover classes', () => {
        const wrapper = mount(TableRow);
        expect(wrapper.find('tr').attributes('class')).toContain('border-b');
        expect(wrapper.find('tr').attributes('class')).toContain('transition-colors');
    });

    it('renders slot content', () => {
        const wrapper = mount(TableRow, {
            slots: { default: '<td data-testid="cell">data</td>' },
        });
        expect(wrapper.find('[data-testid="cell"]').exists()).toBe(true);
    });
});

describe('TableHead', () => {
    it('renders a th element', () => {
        const wrapper = mount(TableHead);
        expect(wrapper.find('th').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TableHead, {
            slots: { default: 'Column Name' },
        });
        expect(wrapper.text()).toContain('Column Name');
    });
});

describe('TableCell', () => {
    it('renders a td element', () => {
        const wrapper = mount(TableCell);
        expect(wrapper.find('td').exists()).toBe(true);
    });

    it('renders slot content', () => {
        const wrapper = mount(TableCell, {
            slots: { default: 'Cell Value' },
        });
        expect(wrapper.text()).toContain('Cell Value');
    });
});
