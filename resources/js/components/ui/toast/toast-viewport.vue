<script setup lang="ts">
import { ToastViewport } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    label: 'Notification',
});
</script>

<template>
    <ToastViewport
        :class="cn('fixed top-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]', props.class)"
        :label="label" />
</template>