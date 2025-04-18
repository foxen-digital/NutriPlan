import { ref } from 'vue';
import axios from 'axios';
import type { Recipe } from '@/types/recipe';

export function useRecipeSearch() {
    const searchQuery = ref('');
    const searchResults = ref<Recipe[]>([]);
    const isSearching = ref(false);
    const selectedRecipe = ref<Recipe | null>(null);

    let searchTimeout: ReturnType<typeof setTimeout>;

    const debounceSearch = () => {
        clearTimeout(searchTimeout);
        if (searchQuery.value) {
            isSearching.value = true;
            searchTimeout = setTimeout(() => {
                searchRecipes();
            }, 300);
        } else {
            searchResults.value = [];
            isSearching.value = false;
        }
    };

    const searchRecipes = async () => {
        try {
            const response = await axios.get(route('api.recipes.search'), {
                params: { query: searchQuery.value },
                withCredentials: true,
            });
            searchResults.value = response.data.data;
        } catch (error) {
            console.error('Error searching recipes:', error);
            searchResults.value = [];
        } finally {
            isSearching.value = false;
        }
    };

    const selectRecipe = (recipe: Recipe) => {
        selectedRecipe.value = recipe;
        searchQuery.value = '';
        searchResults.value = [];
    };

    const clearSelection = () => {
        selectedRecipe.value = null;
        searchQuery.value = '';
        searchResults.value = [];
    };

    return {
        searchQuery,
        searchResults,
        isSearching,
        selectedRecipe,
        debounceSearch,
        selectRecipe,
        clearSelection,
    };
} 