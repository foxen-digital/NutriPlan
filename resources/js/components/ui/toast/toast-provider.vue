<script setup lang="ts">
import { ToastProvider } from 'radix-vue';

const props = defineProps<{
    duration?: number;
    label?: string;
    swipeDirection?: 'up' | 'down' | 'left' | 'right';
}>();
</script>

<template>
    <ToastProvider :duration="duration" :label="label" :swipe-direction="swipeDirection">
        <slot />
    </ToastProvider>
</template>