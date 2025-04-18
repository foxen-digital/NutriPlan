<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import type { MealAssignment } from '@/types/meal-plan';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface Props {
    open: boolean;
    assignment: MealAssignment | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'assignment-updated': [];
}>();

const form = useForm({
    servings: 1,
});

// Reset form when modal opens/closes or assignment changes
watch(
    () => [props.open, props.assignment],
    ([open, assignment]) => {
        if (open && assignment) {
            form.servings = assignment.servings;
        }
    },
    { immediate: true },
);

const closeModal = () => {
    emit('update:open', false);
};

const updateMealAssignment = async () => {
    if (!props.assignment) return;

    await form.put(route('meal-assignments.update', props.assignment.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            emit('assignment-updated');
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Edit Meal Assignment</DialogTitle>
                <DialogDescription>Update the number of servings for this meal.</DialogDescription>
            </DialogHeader>
            <InputError v-if="form.errors.error" :message="form.errors.error" class="mt-2" />

            <form @submit.prevent="updateMealAssignment" class="space-y-4">
                <div>
                    <Label for="edit-servings">Servings</Label>
                    <Input id="edit-servings" v-model="form.servings" type="number" step="1" min="1" max="20" class="mt-1 block w-full" />
                    <InputError :message="form.errors.servings" class="mt-2" />
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Update</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
