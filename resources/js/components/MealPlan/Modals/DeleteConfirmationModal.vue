<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Delete Meal Plan</DialogTitle>
                <DialogDescription>Are you sure you want to delete this meal plan? This action cannot be undone.</DialogDescription>
            </DialogHeader>
            <div class="flex items-center justify-end space-x-2 pt-4">
                <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
                <Button variant="destructive" :disabled="isConfirming" @click="handleConfirm"> Delete </Button>
            </div>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { ref } from 'vue';

defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
}>();

const isConfirming = ref(false);

function handleConfirm() {
    if (isConfirming.value) return;
    isConfirming.value = true;

    // Emit the confirm event
    emit('confirm');

    // Reset after a short delay
    setTimeout(() => {
        isConfirming.value = false;
    }, 500);
}
</script>
