<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import ImportRecipeModal from '@/components/Recipe/ImportRecipeModal.vue';
import RecipeCard from '@/components/Recipe/RecipeCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { DownloadIcon, PlusIcon, UserIcon } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    recipes: {
        data: Array<{
            id: number;
            title: string;
            description: string | null;
            slug: string;
            prep_time: number;
            cooking_time: number;
            servings: number;
            images: string[];
            url: string | null;
            user: {
                name: string;
                slug?: string;
            };
            categories: Array<{
                id: number;
                name: string;
                slug: string;
                recipe_count: number;
            }>;
            is_favorited?: boolean;
        }>;
        links: Array<{
            url?: string;
            label: string;
            active: boolean;
        }>;
    };
    filter?: {
        category?: string;
        show_mine?: boolean;
    };
    auth: {
        user: {
            id: number;
            name: string;
            slug: string;
        };
    };
}

const { recipes, auth } = defineProps<Props>();

const showImportModal = ref(false);
</script>

<template>
    <AppLayout>
        <Head title="Recipes" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">Recipes</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">Browse through our collection of delicious recipes</p>
                </div>
                <div class="mt-4 space-x-4 sm:ml-auto sm:mt-0 sm:flex-none">
                    <Link :href="route('recipes.by-user', { user: auth.user.slug })" class="inline-flex">
                        <Button variant="outline">
                            <UserIcon class="mr-2 h-4 w-4" />
                            My Recipes
                        </Button>
                    </Link>
                    <Button variant="outline" @click="showImportModal = true">
                        <DownloadIcon class="mr-2 h-4 w-4" />
                        Import Recipe
                    </Button>
                    <Link :href="route('recipes.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            New Recipe
                        </Button>
                    </Link>
                </div>

                <ImportRecipeModal v-model:open="showImportModal" />
            </div>

            <div v-if="recipes.data.length === 0" class="mt-16 text-center">
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No recipes</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new recipe</p>
                <div class="mt-6">
                    <Link :href="route('recipes.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            New Recipe
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-else class="mt-8">
                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 xl:gap-x-8">
                    <RecipeCard v-for="recipe in recipes.data" :key="recipe.id" :recipe="recipe" />
                </div>

                <div class="mt-8">
                    <Pagination :links="recipes.links" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
