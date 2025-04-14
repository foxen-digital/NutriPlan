<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { ToastProvider, ToastRegistry, ToastViewport } from '@/components/ui/toast';
import { useRecipeImport } from '@/composables/useRecipeImport';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = ref(true);

onMounted(() => {
    isOpen.value = localStorage.getItem('sidebar') !== 'false';

    // Initialize Echo listeners for real-time recipe import notifications
    const { initializeListeners } = useRecipeImport();
    initializeListeners();
});

const handleSidebarChange = (open: boolean) => {
    isOpen.value = open;
    localStorage.setItem('sidebar', String(open));
};
</script>

<template>
    <ToastProvider>
        <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
            <slot />
        </div>
        <SidebarProvider v-else :default-open="isOpen" :open="isOpen" @update:open="handleSidebarChange">
            <slot />
        </SidebarProvider>

        <ToastViewport class="fixed right-4 top-4 z-50 flex flex-col gap-2" />
        <ToastRegistry />
    </ToastProvider>
</template>
