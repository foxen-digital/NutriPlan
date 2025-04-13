<script setup lang="ts">
import DeleteRecipeModal from '@/components/Recipe/DeleteRecipeModal.vue';
import RecipeForm from '@/components/Recipe/RecipeForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Category } from '@/types/category';
import type { Ingredient } from '@/types/ingredient';
import type { MeasurementUnit, Recipe } from '@/types/recipe';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    recipe: Recipe;
    categories: Category[];
    ingredients: Ingredient[];
    measurementUnits: MeasurementUnit[];
}

const props = defineProps<Props>();

// Use refs to allow adding new items
const categories = ref<Category[]>([...props.categories]);
const ingredients = ref<Ingredient[]>([...props.ingredients]);
const showDeleteModal = ref(false);

const addNewIngredient = (ingredient: { id: number; name: string }) => {
    ingredients.value.push(ingredient as Ingredient);
};

const addNewCategory = (category: { id: number; name: string }) => {
    categories.value.push(category as Category);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Edit ${recipe.title}`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">Edit Recipe</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Update your recipe details below.</p>
                </div>
                <div class="mt-4 sm:ml-4 sm:mt-0">
                    <DeleteRecipeModal v-model:open="showDeleteModal" :recipe-slug="recipe.slug">
                        <Button variant="destructive">Delete Recipe</Button>
                    </DeleteRecipeModal>
                </div>
            </div>

            <div class="mt-8">
                <RecipeForm
                    :recipe="recipe"
                    :categories="categories"
                    :ingredients="ingredients"
                    :measurement-units="measurementUnits"
                    :action="route('recipes.update', recipe.id)"
                    method="put"
                    @submit="(form) => form.put(route('recipes.update', recipe.slug))"
                    @new-ingredient="addNewIngredient"
                    @new-category="addNewCategory"
                />
            </div>
        </div>
    </AppLayout>
</template>
