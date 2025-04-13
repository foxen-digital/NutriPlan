import { ref, type Component } from 'vue';
import { useEventListener } from '@vueuse/core';
import { type ToastVariants } from '.';
import { nanoid } from 'nanoid';

export interface ToastProps {
    id?: string;
    title?: string;
    description?: string;
    variant?: ToastVariants['variant'];
    duration?: number;
    icon?: Component;
    action?: {
        label: string;
        onClick: () => void;
        altText?: string;
    };
}

export interface ToastState {
    id: string;
    title?: string;
    description?: string;
    variant?: ToastVariants['variant'];
    duration?: number;
    icon?: Component;
    action?: {
        label: string;
        onClick: () => void;
        altText?: string;
    };
    open: boolean;
}

export const toasts = ref<ToastState[]>([]);

export function useToast() {
    useEventListener(window, 'keydown', (e: KeyboardEvent) => {
        if (e.key === 'Escape') {
            // Dismiss all toasts when Escape is pressed
            toasts.value.forEach((toast) => {
                dismissToast(toast.id);
            });
        }
    });

    function toast(props: ToastProps) {
        const id = props.id ?? nanoid();
        const newToast = {
            id,
            title: props.title,
            description: props.description,
            variant: props.variant,
            duration: props.duration,
            icon: props.icon,
            action: props.action,
            open: true,
        };

        // Check if toast with this ID already exists
        const toastIndex = toasts.value.findIndex((toast) => toast.id === id);

        if (toastIndex >= 0) {
            // Update existing toast
            toasts.value[toastIndex] = {
                ...toasts.value[toastIndex],
                ...newToast,
            };
        } else {
            // Add new toast
            toasts.value.push(newToast);
        }

        return { id, update, dismiss };
    }

    function dismissToast(id: string) {
        toasts.value = toasts.value.map((toast) => {
            if (toast.id === id) {
                return {
                    ...toast,
                    open: false,
                };
            }
            return toast;
        });
    }

    function update(id: string, props: Partial<ToastProps>) {
        toasts.value = toasts.value.map((toast) => {
            if (toast.id === id) {
                return {
                    ...toast,
                    ...props,
                };
            }
            return toast;
        });
    }

    function dismiss(id: string) {
        dismissToast(id);
    }

    return {
        toast,
        dismissToast,
        toasts,
    };
}

export default useToast; 