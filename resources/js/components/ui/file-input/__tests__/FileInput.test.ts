import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import FileInput from '../FileInput.vue';

vi.mock('lucide-vue-next', () => ({
    UploadIcon: { name: 'UploadIcon', template: '<svg data-testid="upload-icon"></svg>' },
}));

describe('FileInput', () => {
    const defaultProps = { modelValue: null as any };

    it('renders a hidden file input', () => {
        const wrapper = mount(FileInput, { props: defaultProps });
        const input = wrapper.find('input[type="file"]');
        expect(input.exists()).toBe(true);
        expect(input.classes()).toContain('hidden');
    });

    it('renders the upload drop zone', () => {
        const wrapper = mount(FileInput, { props: defaultProps });
        expect(wrapper.find('.border-dashed').exists()).toBe(true);
    });

    it('renders upload icon', () => {
        const wrapper = mount(FileInput, { props: defaultProps });
        expect(wrapper.find('[data-testid="upload-icon"]').exists()).toBe(true);
    });

    it('shows "Upload a file" for single file mode', () => {
        const wrapper = mount(FileInput, {
            props: { modelValue: null as any, multiple: false },
        });
        expect(wrapper.text()).toContain('Upload a file');
    });

    it('shows "Upload multiple files" for multiple mode', () => {
        const wrapper = mount(FileInput, {
            props: { modelValue: null as any, multiple: true },
        });
        expect(wrapper.text()).toContain('Upload multiple files');
    });

    it('passes accept prop to the file input', () => {
        const wrapper = mount(FileInput, {
            props: { modelValue: null as any, accept: 'image/*' },
        });
        expect(wrapper.find('input[type="file"]').attributes('accept')).toBe('image/*');
    });

    it('emits update:modelValue with single file on change', async () => {
        const wrapper = mount(FileInput, { props: defaultProps });
        const file = new File(['content'], 'test.jpg', { type: 'image/jpeg' });

        const input = wrapper.find('input[type="file"]');
        Object.defineProperty(input.element, 'files', {
            value: [file],
            configurable: true,
        });

        await input.trigger('change');
        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')![0][0]).toBe(file);
    });

    it('emits update:modelValue with array of files in multiple mode', async () => {
        const wrapper = mount(FileInput, {
            props: { modelValue: null as any, multiple: true },
        });
        const file1 = new File(['a'], 'a.jpg', { type: 'image/jpeg' });
        const file2 = new File(['b'], 'b.jpg', { type: 'image/jpeg' });

        const input = wrapper.find('input[type="file"]');
        Object.defineProperty(input.element, 'files', {
            value: [file1, file2],
            configurable: true,
        });

        await input.trigger('change');
        expect(wrapper.emitted('update:modelValue')![0][0]).toEqual([file1, file2]);
    });

    it('handles drop event with a single file', async () => {
        const wrapper = mount(FileInput, { props: defaultProps });
        const file = new File(['content'], 'dropped.jpg', { type: 'image/jpeg' });

        const dropZone = wrapper.find('.border-dashed');
        await dropZone.trigger('drop', {
            dataTransfer: { files: [file] },
        });

        expect(wrapper.emitted('update:modelValue')).toBeTruthy();
    });
});
