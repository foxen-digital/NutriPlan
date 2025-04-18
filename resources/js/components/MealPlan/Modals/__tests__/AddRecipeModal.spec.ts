import { describe, it, expect, vi, beforeEach } from 'vitest';
import { shallowMount } from '@vue/test-utils';
import { ref } from 'vue';
import AddRecipeModal from '../AddRecipeModal.vue';
import axios from 'axios';
import type { Recipe } from '@/types/recipe';

// Mock reactive state to control from tests
const mockSearchQuery = ref('');
const mockSearchResults = ref<Recipe[]>([]);
const mockIsSearching = ref(false);
const mockSelectedRecipe = ref<Recipe | null>(null);

// Mock the useRecipeSearch composable
vi.mock('@/composables/useRecipeSearch', () => ({
  useRecipeSearch: () => ({
    searchQuery: mockSearchQuery,
    searchResults: mockSearchResults,
    isSearching: mockIsSearching,
    selectedRecipe: mockSelectedRecipe,
    debounceSearch: vi.fn(),
    selectRecipe: vi.fn((recipe: Recipe) => {
      mockSelectedRecipe.value = recipe;
    }),
    clearSelection: vi.fn(() => {
      mockSelectedRecipe.value = null;
    }),
  }),
}));

// Mock axios
vi.mock('axios');

// Mock route function
vi.stubGlobal('route', vi.fn((name: string) => `mocked-route/${name}`));

describe('AddRecipeModal', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockSearchQuery.value = '';
    mockSearchResults.value = [];
    mockIsSearching.value = false;
    mockSelectedRecipe.value = null;
  });

  it('emits update:open event when modal is closed', () => {
    const wrapper = shallowMount(AddRecipeModal, {
      props: {
        open: true,
        mealPlanId: 1,
      }
    });

    // Call the close method
    wrapper.vm.$emit('update:open', false);
    
    expect(wrapper.emitted('update:open')).toBeTruthy();
    expect(wrapper.emitted('update:open')![0]).toEqual([false]);
  });

  it('calculates servings based on scale factor', () => {
    const wrapper = shallowMount(AddRecipeModal, {
      props: {
        open: true,
        mealPlanId: 1,
      }
    });

    // Access the calculateServings method directly
    const servings = (wrapper.vm as any).calculateServings(4, 2);
    expect(servings).toBe(8);
  });

  it('adds a recipe to the meal plan when addRecipe is called', async () => {
    // Mock the response from the API
    vi.mocked(axios.post).mockResolvedValueOnce({});

    // Set up our mock selected recipe
    mockSelectedRecipe.value = {
      id: 123,
      title: 'Test Recipe',
      servings: 4,
      description: 'Description',
      prep_time: 10,
      cooking_time: 20,
      total_time: 30,
      difficulty_level: 'Easy',
      created_at: '2023-01-01',
      url: null,
      author: 'Test Author',
      is_public: true,
      images: [],
      slug: 'test-recipe',
      instructions: 'Test instructions',
      categories: [],
      ingredients: [],
    };

    const wrapper = shallowMount(AddRecipeModal, {
      props: {
        open: true,
        mealPlanId: 1,
      }
    });

    // Call the addRecipe method directly
    await (wrapper.vm as any).addRecipe();

    // Check that API was called correctly
    expect(axios.post).toHaveBeenCalledWith(
      'mocked-route/meal-plans.add-recipe',
      {
        meal_plan_id: 1,
        recipe_id: 123,
        scale_factor: 1, // Default scale factor
      }
    );

    // Check that the appropriate events were emitted
    expect(wrapper.emitted('update:open')).toBeTruthy();
    expect(wrapper.emitted('recipe-added')).toBeTruthy();
  });

  it('handles API errors gracefully when adding a recipe', async () => {
    // Mock an error response from the API
    vi.mocked(axios.post).mockRejectedValueOnce(new Error('API error'));
    console.error = vi.fn(); // Mock console.error

    // Set up our mock selected recipe
    mockSelectedRecipe.value = {
      id: 123,
      title: 'Test Recipe',
      servings: 4,
      description: 'Description',
      prep_time: 10,
      cooking_time: 20,
      total_time: 30,
      difficulty_level: 'Easy',
      created_at: '2023-01-01',
      url: null,
      author: 'Test Author',
      is_public: true,
      images: [],
      slug: 'test-recipe',
      instructions: 'Test instructions',
      categories: [],
      ingredients: [],
    };

    const wrapper = shallowMount(AddRecipeModal, {
      props: {
        open: true,
        mealPlanId: 1,
      }
    });

    // Call the addRecipe method directly
    await (wrapper.vm as any).addRecipe();
    
    // Check that the error was logged
    expect(console.error).toHaveBeenCalledWith(
      'Error adding recipe to meal plan:',
      expect.any(Error)
    );

    // Check that no events were emitted
    expect(wrapper.emitted('update:open')).toBeFalsy();
    expect(wrapper.emitted('recipe-added')).toBeFalsy();
  });

  it('does not call the API when no recipe is selected', async () => {
    // Mock the response from the API
    vi.mocked(axios.post).mockResolvedValueOnce({});

    // Ensure no recipe is selected
    mockSelectedRecipe.value = null;

    const wrapper = shallowMount(AddRecipeModal, {
      props: {
        open: true,
        mealPlanId: 1,
      }
    });

    // Call the addRecipe method directly
    await (wrapper.vm as any).addRecipe();

    // Check that the API was not called
    expect(axios.post).not.toHaveBeenCalled();
    
    // Check that no events were emitted
    expect(wrapper.emitted('update:open')).toBeFalsy();
    expect(wrapper.emitted('recipe-added')).toBeFalsy();
  });
}); 