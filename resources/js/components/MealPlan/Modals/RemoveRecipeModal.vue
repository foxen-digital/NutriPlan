<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { Recipe } from '@/types/recipe';
import { router } from '@inertiajs/vue3';

interface Props {
    open: boolean;
    recipe: Recipe | null;
    mealPlanId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const closeModal = () => {
    emit('update:open', false);
};

const removeRecipe = () => {
    if (!props.recipe) return;

    router.delete(
        route('meal-plans.remove-recipe', {
            id: props.mealPlanId,
            recipeId: props.recipe.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
            },
        },
    );
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Remove Recipe</DialogTitle>
                <DialogDescription> Are you sure you want to remove "{{ recipe?.title }}" from this meal plan? </DialogDescription>
            </DialogHeader>
            <div class="flex items-center justify-end space-x-2 pt-4">
                <Button variant="outline" @click="closeModal">Cancel</Button>
                <Button variant="destructive" @click="removeRecipe">Remove</Button>
            </div>
        </DialogContent>
    </Dialog>
</template>
