<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import ImportRecipeModal from '@/components/Recipe/ImportRecipeModal.vue';
import RecipeCard from '@/components/Recipe/RecipeCard.vue';
import RecipeSearchModal from '@/components/Recipe/RecipeSearchModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import type { RecipeFilters } from '@/types/recipes';
import { Head, Link, router } from '@inertiajs/vue3';
import { DownloadIcon, PlusIcon, SearchIcon, XCircleIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
    filter?: RecipeFilters;
    user: {
        id: number;
        name: string;
        slug: string;
    };
    isOwner: boolean;
}

const props = defineProps<Props>();

const pageTitle = computed(() => (props.isOwner ? 'My Recipes' : `${props.user.name}'s Recipes`));
const currentFilters = computed(() => props.filter || {});

const showImportModal = ref(false);
const showSearchModal = ref(false);

const clearSearch = () => {
    const queryParams = { ...route().params };
    delete queryParams.search_term;
    delete queryParams.search_mode;
    delete queryParams.page;

    router.get(
        route('recipes.by-user', { user: props.user.slug, ...queryParams }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};
</script>

<template>
    <AppLayout>
        <Head :title="pageTitle" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">{{ pageTitle }}</h1>
                    <p v-if="isOwner" class="mt-2 text-sm text-gray-700 dark:text-gray-400">Manage your own recipe collection</p>
                    <p v-else class="mt-2 text-sm text-gray-700 dark:text-gray-400">Browse recipes created by {{ user.name }}</p>
                </div>
                <div class="mt-4 space-x-4 sm:ml-auto sm:mt-0 sm:flex-none">
                    <Button variant="outline" @click="showSearchModal = true" class="rounded-full px-2">
                        <SearchIcon class="h-4 w-4" />
                    </Button>
                    <Link :href="route('recipes.index')">
                        <Button variant="outline">All Recipes</Button>
                    </Link>
                    <Button variant="outline" v-if="isOwner" @click="showImportModal = true">
                        <DownloadIcon class="mr-2 h-4 w-4" />
                        Import Recipe
                    </Button>
                    <Link v-if="isOwner" :href="route('recipes.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            New Recipe
                        </Button>
                    </Link>
                </div>
            </div>

            <div v-if="recipes.data.length === 0" class="mt-16 text-center">
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No recipes found</h3>
                <p v-if="!currentFilters.search_term && isOwner" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Get started by creating a new recipe
                </p>
                <p v-else-if="!currentFilters.search_term && !isOwner" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ user.name }} hasn't shared any recipes yet
                </p>
                <p v-else class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try broadening your search criteria.</p>
                <div v-if="currentFilters.search_term" class="mt-6">
                    <Button variant="outline" @click="clearSearch"> Clear Search </Button>
                </div>
                <div v-else-if="isOwner" class="mt-6">
                    <Link :href="route('recipes.create')">
                        <Button>
                            <PlusIcon class="mr-2 h-4 w-4" />
                            New Recipe
                        </Button>
                    </Link>
                </div>
                <div v-else class="mt-6">
                    <Link :href="route('recipes.index')">
                        <Button> Explore All Recipes </Button>
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

            <ImportRecipeModal v-model:open="showImportModal" />
            <RecipeSearchModal v-model:open="showSearchModal" :current-filters="currentFilters" />

            <div v-if="currentFilters.search_term" class="my-4 flex items-center justify-center">
                <span
                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-200"
                >
                    Searching for: "{{ currentFilters.search_term }}"
                    <button
                        @click="clearSearch"
                        type="button"
                        class="ml-2 inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full text-gray-400 hover:bg-gray-200 hover:text-gray-500 focus:bg-gray-500 focus:text-white focus:outline-none dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:bg-gray-600"
                    >
                        <span class="sr-only">Remove search</span>
                        <XCircleIcon class="h-3 w-3" />
                    </button>
                </span>
            </div>
        </div>
    </AppLayout>
</template>
