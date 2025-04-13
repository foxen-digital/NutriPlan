<script setup lang="ts">
import { ToastRoot, type ToastRootEmits } from 'radix-vue';
import { cn } from '@/lib/utils';
import { toastVariants, type ToastVariants } from '.';
import ToastClose from './toast-close.vue';

interface Props {
    class?: string;
    variant?: ToastVariants['variant'];
    duration?: number;
    onOpenChange?: (open: boolean) => void;
    forceMount?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const emits = defineEmits<ToastRootEmits>();
</script>

<template>
    <ToastRoot :class="cn(toastVariants({ variant }), props.class)" :duration="duration" :force-mount="forceMount"
        @escapeKeyDown="(event) => emits('escapeKeyDown', event)" @pauseHover="(event) => emits('pauseHover', event)"
        @resumeHover="(event) => emits('resumeHover', event)" @swipeCancel="(event) => emits('swipeCancel', event)"
        @swipeEnd="(event) => emits('swipeEnd', event)" @swipeMove="(event, info) => emits('swipeMove', event, info)"
        @swipeStart="(event, info) => emits('swipeStart', event, info)" @update:open="(open) => {
            emits('update:open', open);
            onOpenChange?.(open);
        }">
        <div class="grid gap-1">
            <slot />
        </div>
        <slot name="close">
            <ToastClose />
        </slot>
    </ToastRoot>
</template>