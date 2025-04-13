<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useToast } from '@/components/ui/toast';
import AppLayout from '@/layouts/AppLayout.vue';

const { toast } = useToast();

function showSuccessToast() {
    toast({
        title: 'Success',
        description: 'Your action was completed successfully.',
        variant: 'success',
    });
}

function showErrorToast() {
    toast({
        title: 'Error',
        description: 'There was a problem with your action.',
        variant: 'destructive',
    });
}

function showInfoToast() {
    toast({
        title: 'Information',
        description: 'Here is some useful information for you.',
        variant: 'info',
    });
}

function showWarningToast() {
    toast({
        title: 'Warning',
        description: 'Be careful with what you are doing.',
        variant: 'warning',
    });
}

function showActionToast() {
    toast({
        title: 'Action Required',
        description: 'You need to save your work.',
        variant: 'default',
        action: {
            label: 'Save Now',
            onClick: () => console.log('Saving...'),
            altText: 'Save your work now',
        },
    });
}
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Home', href: '/' },
            { title: 'Demo', href: '/demo' },
            { title: 'Toasts', href: '/demo/toasts' },
        ]"
    >
        <div class="p-6">
            <div class="mb-6">
                <h1 class="mb-2 text-2xl font-bold">Toast Notifications Demo</h1>
                <p class="text-gray-600">Click the buttons below to trigger different toast notifications.</p>
            </div>

            <div class="flex flex-wrap gap-4">
                <Button @click="showSuccessToast" variant="default">Show Success Toast</Button>
                <Button @click="showErrorToast" variant="destructive">Show Error Toast</Button>
                <Button @click="showInfoToast" variant="secondary">Show Info Toast</Button>
                <Button @click="showWarningToast" variant="outline">Show Warning Toast</Button>
                <Button @click="showActionToast">Show Action Toast</Button>
            </div>
        </div>
    </AppLayout>
</template>
