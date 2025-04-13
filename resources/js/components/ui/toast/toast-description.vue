<script setup lang="ts">
import { ToastDescription } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <ToastDescription :class="cn('text-sm opacity-90', props.class)">
        <slot />
    </ToastDescription>
</template>