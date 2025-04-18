<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Edit Scale Factor</DialogTitle>
                <DialogDescription> Adjust the scale factor for "{{ recipe?.title }}" </DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div>
                    <Label for="edit-scale-factor">Scale Factor</Label>
                    <Input id="edit-scale-factor" v-model.number="scaleFactor" type="number" min="0.5" max="10" step="0.5" />
                    <p class="mt-1 text-xs text-gray-500">
                        This will make approximately {{ recipe ? calculateServings(recipe.servings, scaleFactor) : 0 }} servings
                    </p>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
                <Button @click="save">Save Changes</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Recipe } from '@/types/recipe';
import { ref, watch } from 'vue';

interface RecipeWithPivot extends Recipe {
    pivot: {
        id: number;
        scale_factor: number;
        servings_available: number;
    };
}

interface Props {
    open: boolean;
    recipe: RecipeWithPivot | null;
    mealPlanId: number;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'save', recipe: RecipeWithPivot, scaleFactor: number): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const scaleFactor = ref(1.0);

watch(
    () => props.recipe,
    (newRecipe) => {
        if (newRecipe) {
            scaleFactor.value = newRecipe.pivot.scale_factor;
        }
    },
    { immediate: true },
);

const calculateServings = (originalServings: number, scaleFactor: number): number => {
    return Math.round(originalServings * scaleFactor);
};

const save = () => {
    if (!props.recipe) return;
    emit('save', props.recipe, scaleFactor.value);
};
</script>
