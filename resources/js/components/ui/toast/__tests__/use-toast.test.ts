import { describe, it, expect, beforeEach } from 'vitest';
import { useToast, toasts } from '../use-toast';

describe('useToast', () => {
    beforeEach(() => {
        // Reset toasts before each test
        toasts.value = [];
    });

    it('should create a toast', () => {
        const { toast } = useToast();

        toast({
            title: 'Test Toast',
            description: 'This is a test toast',
        });

        expect(toasts.value).toHaveLength(1);
        expect(toasts.value[0].title).toBe('Test Toast');
        expect(toasts.value[0].description).toBe('This is a test toast');
        expect(toasts.value[0].open).toBe(true);
    });

    it('should update an existing toast', () => {
        const { toast } = useToast();

        const { id } = toast({
            id: 'test-id',
            title: 'Original Title',
            description: 'Original description',
        });

        toast({
            id,
            title: 'Updated Title',
            description: 'Updated description',
        });

        expect(toasts.value).toHaveLength(1);
        expect(toasts.value[0].title).toBe('Updated Title');
        expect(toasts.value[0].description).toBe('Updated description');
    });

    it('should dismiss a toast', () => {
        const { toast, dismissToast } = useToast();

        const { id } = toast({
            title: 'Test Toast',
        });

        dismissToast(id);

        expect(toasts.value[0].open).toBe(false);
    });

    it('should support different variants', () => {
        const { toast } = useToast();

        toast({
            title: 'Success Toast',
            variant: 'success',
        });

        toast({
            title: 'Error Toast',
            variant: 'destructive',
        });

        toast({
            title: 'Info Toast',
            variant: 'info',
        });

        toast({
            title: 'Warning Toast',
            variant: 'warning',
        });

        expect(toasts.value).toHaveLength(4);
        expect(toasts.value[0].variant).toBe('success');
        expect(toasts.value[1].variant).toBe('destructive');
        expect(toasts.value[2].variant).toBe('info');
        expect(toasts.value[3].variant).toBe('warning');
    });
}); 