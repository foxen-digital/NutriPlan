<script setup lang="ts">
import { ToastClose } from 'radix-vue';
import { cn } from '@/lib/utils';

interface Props {
    class?: string;
}

const props = defineProps<Props>();
</script>

<template>
    <ToastClose :class="cn(
        'absolute right-1 top-1 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-1 group-hover:opacity-100',
        props.class
    )
        ">
        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18" />
            <line x1="6" y1="6" x2="18" y2="18" />
        </svg>
        <span class="sr-only">Close</span>
    </ToastClose>
</template>