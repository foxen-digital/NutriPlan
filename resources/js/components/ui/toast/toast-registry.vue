<script setup lang="ts">
import { computed, nextTick, watch } from 'vue';
import { Toast, ToastAction, ToastClose, ToastDescription, ToastTitle } from '.';
import { toasts } from './use-toast';

const activeToasts = computed(() => {
    return toasts.value.filter((toast) => toast.open);
});

// Remove dismissed toasts after animation finishes
watch(
    toasts,
    () => {
        const closed = toasts.value.filter((toast) => !toast.open);
        if (closed.length <= 0) return;

        const timeout = setTimeout(() => {
            toasts.value = toasts.value.filter((toast) => toast.open);
            clearTimeout(timeout);
        }, 1000);
    },
    { deep: true }
);
</script>

<template>
    <template v-for="toast in activeToasts" :key="toast.id">
        <Toast :variant="toast.variant" :duration="toast.duration" @update:open="(open: boolean) => {
            if (!open) {
                const toastIndex = toasts.findIndex((t) => t.id === toast.id);
                if (toastIndex >= 0) {
                    toasts[toastIndex].open = false;
                }
            }
        }">
            <div class="flex gap-3">
                <component :is="toast.icon" v-if="toast.icon" class="h-4 w-4 shrink-0" />
                <div class="flex-1">
                    <ToastTitle v-if="toast.title">{{ toast.title }}</ToastTitle>
                    <ToastDescription v-if="toast.description">{{ toast.description }}</ToastDescription>
                </div>
            </div>
            <ToastAction v-if="toast.action" :alt-text="toast.action.altText || 'Action'" @click="toast.action.onClick">
                {{ toast.action.label }}
            </ToastAction>
            <ToastClose />
        </Toast>
    </template>
</template>