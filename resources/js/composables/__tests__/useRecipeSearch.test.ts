import type { Recipe } from '@/types/recipe';
import axios from 'axios';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { useRecipeSearch } from '../useRecipeSearch';

vi.mock('axios');

// Mock the global route function
global.route = vi.fn((name: string) => `mocked-route/${name}`);

describe('useRecipeSearch', () => {
    const mockRecipe: Recipe = {
        id: 1,
        title: 'Test Recipe',
        description: 'A test recipe description',
        servings: 4,
        prep_time: 30,
        cooking_time: 45,
        total_time: 75,
        difficulty_level: 'Medium',
        created_at: '2024-03-20T12:00:00.000Z',
        url: 'https://example.com/recipe',
        author: 'Test Author',
        is_public: true,
        images: ['test-image.jpg'],
        slug: 'test-recipe',
        instructions: 'Test instructions',
        categories: [
            {
                id: 1,
                name: 'Test Category',
                slug: 'test-category',
            },
        ],
        ingredients: [
            {
                id: 1,
                name: 'Test Ingredient',
                pivot: {
                    amount: 1,
                    unit: 'cup',
                    description: 'Test Description',
                },
            },
        ],
    };

    beforeEach(() => {
        vi.clearAllMocks();
        vi.useFakeTimers();
    });

    it('initializes with empty state', () => {
        const { searchQuery, searchResults, isSearching, selectedRecipe } = useRecipeSearch();

        expect(searchQuery.value).toBe('');
        expect(searchResults.value).toEqual([]);
        expect(isSearching.value).toBe(false);
        expect(selectedRecipe.value).toBeNull();
    });

    it('debounces search and calls API', async () => {
        const { searchQuery, searchResults, isSearching, debounceSearch } = useRecipeSearch();
        const mockResponse = { data: { data: [mockRecipe] } };

        vi.mocked(axios.get).mockResolvedValueOnce(mockResponse);

        searchQuery.value = 'test';
        debounceSearch();

        expect(isSearching.value).toBe(true);
        expect(axios.get).not.toHaveBeenCalled();

        await vi.advanceTimersByTimeAsync(300);

        expect(axios.get).toHaveBeenCalledWith('mocked-route/api.recipes.search', {
            params: { query: 'test' },
            withCredentials: true,
        });
        expect(searchResults.value).toEqual([mockRecipe]);
        expect(isSearching.value).toBe(false);
    });

    it('clears search results when query is empty', () => {
        const { searchQuery, searchResults, isSearching, debounceSearch } = useRecipeSearch();

        searchResults.value = [mockRecipe];
        isSearching.value = true;
        searchQuery.value = '';

        debounceSearch();

        expect(searchResults.value).toEqual([]);
        expect(isSearching.value).toBe(false);
    });

    it('handles API error gracefully', async () => {
        const { searchQuery, searchResults, isSearching, debounceSearch } = useRecipeSearch();

        vi.mocked(axios.get).mockRejectedValueOnce(new Error('API Error'));

        searchQuery.value = 'test';
        debounceSearch();

        await vi.advanceTimersByTimeAsync(300);

        expect(searchResults.value).toEqual([]);
        expect(isSearching.value).toBe(false);
    });

    it('selects a recipe correctly', () => {
        const { selectedRecipe, searchQuery, searchResults, selectRecipe } = useRecipeSearch();

        searchQuery.value = 'test';
        searchResults.value = [mockRecipe];

        selectRecipe(mockRecipe);

        expect(selectedRecipe.value).toEqual(mockRecipe);
        expect(searchQuery.value).toBe('');
        expect(searchResults.value).toEqual([]);
    });

    it('clears selection correctly', () => {
        const { selectedRecipe, searchQuery, searchResults, clearSelection } = useRecipeSearch();

        selectedRecipe.value = mockRecipe;
        searchQuery.value = 'test';
        searchResults.value = [mockRecipe];

        clearSelection();

        expect(selectedRecipe.value).toBeNull();
        expect(searchQuery.value).toBe('');
        expect(searchResults.value).toEqual([]);
    });
});
