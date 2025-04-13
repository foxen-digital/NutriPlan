import { cva, type VariantProps } from 'class-variance-authority';
import { type ToastRootProps } from 'radix-vue';
import { type Component } from 'vue';

export { default as ToastProvider } from './toast-provider.vue';
export { default as ToastViewport } from './toast-viewport.vue';
export { default as Toast } from './toast.vue';
export { default as ToastTitle } from './toast-title.vue';
export { default as ToastDescription } from './toast-description.vue';
export { default as ToastAction } from './toast-action.vue';
export { default as ToastClose } from './toast-close.vue';
export { default as ToastRegistry } from './toast-registry.vue';
export { default as useToast } from './use-toast';

export const toastVariants = cva(
    'group pointer-events-auto relative flex w-full items-center justify-between space-x-2 overflow-hidden rounded-md border p-4 pr-6 shadow-lg transition-all data-[swipe=cancel]:translate-x-0 data-[swipe=end]:translate-x-[var(--radix-toast-swipe-end-x)] data-[swipe=move]:translate-x-[var(--radix-toast-swipe-move-x)] data-[swipe=move]:transition-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[swipe=end]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full',
    {
        variants: {
            variant: {
                default: 'border bg-background text-foreground',
                success: 'success group border-success bg-success text-success-foreground',
                destructive:
                    'destructive group border-destructive bg-destructive text-destructive-foreground',
                info: 'info group border-info bg-info text-info-foreground',
                warning: 'warning group border-warning bg-warning text-warning-foreground',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    }
);

export type ToastVariants = VariantProps<typeof toastVariants>;

export interface Toast extends ToastRootProps {
    id: string;
    title?: string;
    description?: string;
    action?: {
        label: string;
        onClick: () => void;
        altText?: string;
    };
    variant?: ToastVariants['variant'];
    icon?: Component;
} 