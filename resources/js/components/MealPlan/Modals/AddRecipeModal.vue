<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Recipe to Meal Plan</DialogTitle>
                <DialogDescription>Search for a recipe to add to your meal plan.</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 py-4">
                <div class="space-y-2">
                    <Label for="recipe-search">Search Recipes</Label>
                    <div class="relative">
                        <Input id="recipe-search" v-model="searchQuery" placeholder="Type to search..." @input="debounceSearch" />
                        <div v-if="isSearching" class="absolute right-3 top-2.5">
                            <Spinner class="h-5 w-5 text-gray-400" />
                        </div>
                    </div>
                </div>

                <div v-if="searchResults.length > 0" class="max-h-60 overflow-y-auto rounded-md border p-2 dark:border-gray-700">
                    <div
                        v-for="recipe in searchResults"
                        :key="recipe.id"
                        class="cursor-pointer rounded-md p-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                        @click="selectRecipe(recipe)"
                    >
                        <div class="flex items-center gap-3">
                            <div v-if="recipe.images && recipe.images.length > 0" class="h-10 w-10 overflow-hidden rounded-md">
                                <img :src="recipe.images[0]" alt="" class="h-full w-full object-cover" />
                            </div>
                            <div>
                                <p class="font-medium">{{ recipe.title }}</p>
                                <p class="text-xs text-gray-500">{{ recipe.servings }} servings</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="searchQuery && !isSearching && searchResults.length === 0" class="rounded-md bg-gray-50 p-3 dark:bg-gray-800">
                    <p class="text-center text-sm text-gray-500">No recipes found matching your search.</p>
                </div>

                <div v-if="selectedRecipe" class="rounded-md border p-3 dark:border-gray-700">
                    <h3 class="font-medium">{{ selectedRecipe.title }}</h3>
                    <div class="mt-3 space-y-2">
                        <div>
                            <Label for="scale-factor">Scale Factor</Label>
                            <Input id="scale-factor" v-model.number="scaleFactor" type="number" min="0.5" max="10" step="0.5" />
                            <p class="mt-1 text-xs text-gray-500">
                                This will make approximately
                                {{ calculateServings(selectedRecipe.servings, scaleFactor) }} servings
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="$emit('update:open', false)">Cancel</Button>
                <Button :disabled="!selectedRecipe" @click="addRecipe">Add Recipe</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import Spinner from '@/components/ui/spinner.vue';
import { useRecipeSearch } from '@/composables/useRecipeSearch';
import axios from 'axios';
import { ref } from 'vue';

interface Props {
    open: boolean;
    mealPlanId: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'recipe-added': [];
}>();

const { searchQuery, searchResults, isSearching, selectedRecipe, debounceSearch, selectRecipe, clearSelection } = useRecipeSearch();

const scaleFactor = ref(1.0);

const calculateServings = (originalServings: number, scaleFactor: number): number => {
    return Math.round(originalServings * scaleFactor);
};

const addRecipe = async () => {
    if (!selectedRecipe.value) return;

    try {
        await axios.post(route('meal-plans.add-recipe'), {
            meal_plan_id: props.mealPlanId,
            recipe_id: selectedRecipe.value.id,
            scale_factor: scaleFactor.value,
        });

        emit('update:open', false);
        emit('recipe-added');

        // Reset form state
        clearSelection();
        scaleFactor.value = 1.0;
    } catch (error) {
        console.error('Error adding recipe to meal plan:', error);
    }
};
</script>
