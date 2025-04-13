import { ToastProps } from './use-toast';
import { FunctionalComponent, Ref, Component } from 'vue';

export { ToastProps } from './use-toast';
export interface ToastState extends ToastProps {
    open: boolean;
}

declare module '@/components/ui/toast' {
    export const ToastProvider: Component;
    export const ToastViewport: Component;
    export const Toast: Component;
    export const ToastTitle: Component;
    export const ToastDescription: Component;
    export const ToastAction: Component;
    export const ToastClose: Component;
    export const ToastRegistry: Component;

    export function useToast(): {
        toast: (props: ToastProps) => {
            id: string;
            update: (id: string, props: Partial<ToastProps>) => void;
            dismiss: (id: string) => void;
        };
        dismissToast: (id: string) => void;
        toasts: Ref<ToastState[]>;
    };
} 