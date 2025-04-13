<script setup lang="ts">
import { ToastTitle } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <ToastTitle :class="cn('text-sm font-semibold', props.class)">
        <slot />
    </ToastTitle>
</template>