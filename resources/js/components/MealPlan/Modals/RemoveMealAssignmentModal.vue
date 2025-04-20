<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { MealAssignment } from '@/types/meal-plan';

interface Props {
    open: boolean;
    assignment: MealAssignment | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();

const closeModal = () => {
    emit('update:open', false);
};

const confirmRemoval = () => {
    if (!props.assignment) return;
    emit('confirm');
    // closeModal(); // Keep modal open until action is successful
};

// Helper to get recipe title
const getRecipeTitle = (assignment: MealAssignment | null): string => {
    return assignment?.meal_plan_recipe?.recipe?.title ?? 'this meal';
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Remove Meal Assignment</DialogTitle>
                <DialogDescription> Are you sure you want to remove "{{ getRecipeTitle(assignment) }}" from this day? </DialogDescription>
            </DialogHeader>
            <div class="flex items-center justify-end space-x-2 pt-4">
                <Button variant="outline" @click="closeModal">Cancel</Button>
                <Button variant="destructive" @click="confirmRemoval">Remove</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
