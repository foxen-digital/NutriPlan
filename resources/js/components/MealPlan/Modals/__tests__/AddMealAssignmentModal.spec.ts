import type { MealPlanDay } from '@/types/meal-plan';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import AddMealAssignmentModal from '../AddMealAssignmentModal.vue';

// Create a shared mock instance for useForm
const mockForm = {
    meal_plan_day_id: '',
    meal_plan_recipe_id: '',
    servings: 1,
    to_cook: false,
    errors: {},
    processing: false,
    post: vi.fn().mockImplementation(() => Promise.resolve()),
    reset: vi.fn(),
};

// Mock useForm to return the shared instance
vi.mock('@inertiajs/vue3', async (importOriginal) => {
    const actual = await importOriginal<typeof import('@inertiajs/vue3')>();
    return {
        ...actual,
        useForm: vi.fn(() => mockForm),
    };
});

// Mock route function
vi.stubGlobal('route', (name: string, params?: any) => {
    return `/api/route/${name}`;
});

describe('AddMealAssignmentModal', () => {
    // Create stubs for UI components
    const stubs = {
        Dialog: {
            template: '<div class="dialog"><slot></slot></div>',
            props: ['open'],
        },
        DialogContent: {
            template: '<div class="dialog-content"><slot></slot></div>',
        },
        DialogHeader: {
            template: '<div class="dialog-header"><slot></slot></div>',
        },
        DialogTitle: {
            template: '<h2 class="dialog-title"><slot></slot></h2>',
        },
        DialogDescription: {
            template: '<p class="dialog-description"><slot></slot></p>',
        },
        DialogFooter: {
            template: '<div class="dialog-footer"><slot></slot></div>',
        },
        Button: {
            template: '<button :class="variant" :type="type"><slot></slot></button>',
            props: ['variant', 'type'],
        },
        Label: {
            template: '<label :class="className"><slot></slot></label>',
            props: ['className'],
        },
        Input: {
            template:
                '<input :id="id" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" :type="type" :min="min" :max="max" :step="step" :class="className" />',
            props: ['id', 'modelValue', 'type', 'min', 'max', 'step', 'className'],
            emits: ['update:modelValue'],
        },
        Select: {
            template:
                '<select :id="id" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)" :class="className"><option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option></select>',
            props: ['id', 'modelValue', 'options', 'className'],
            emits: ['update:modelValue'],
        },
        Checkbox: {
            template: '<input type="checkbox" :id="id" :checked="modelValue" @change="$emit(\'update:modelValue\', $event.target.checked)" />',
            props: ['id', 'modelValue'],
            emits: ['update:modelValue'],
        },
        InputError: {
            template: '<div v-if="message" class="input-error">{{ message }}</div>',
            props: ['message'],
        },
    };

    const mockDay: MealPlanDay = {
        id: 1,
        day_number: 1,
        meal_plan_id: 1,
        date: '2023-01-01',
        created_at: '2023-01-01T00:00:00.000Z',
        updated_at: '2023-01-01T00:00:00.000Z',
        meal_assignments: [],
    };

    const mockAvailableRecipes = [
        { value: '1', label: 'Recipe 1 (4 servings available)' },
        { value: '2', label: 'Recipe 2 (6 servings available)' },
    ];

    beforeEach(() => {
        // Reset the mock form before each test
        vi.clearAllMocks();
        mockForm.reset();
        mockForm.errors = {};
    });

    it('initializes form with correct values when opened', async () => {
        const wrapper = mount(AddMealAssignmentModal, {
            props: {
                open: true,
                mealPlanDay: mockDay,
                availableRecipes: mockAvailableRecipes,
            },
            global: {
                stubs,
            },
        });

        // Check that form fields are rendered
        const recipeSelect = wrapper.find('select');
        const servingsInput = wrapper.find('input[type="number"]');
        const toCookCheckbox = wrapper.find('input[type="checkbox"]');

        expect(recipeSelect.exists()).toBe(true);
        expect(servingsInput.exists()).toBe(true);
        expect(toCookCheckbox.exists()).toBe(true);
    });

    it('displays correct title and description', () => {
        const wrapper = mount(AddMealAssignmentModal, {
            props: {
                open: true,
                mealPlanDay: mockDay,
                availableRecipes: mockAvailableRecipes,
            },
            global: {
                stubs,
            },
        });

        const title = wrapper.find('.dialog-title');
        const description = wrapper.find('.dialog-description');

        expect(title.text()).toBe('Add Meal to Day');
        expect(description.text()).toContain('Select a recipe and specify the number of servings');
    });

    it('emits update:open event when cancel button is clicked', async () => {
        const wrapper = mount(AddMealAssignmentModal, {
            props: {
                open: true,
                mealPlanDay: mockDay,
                availableRecipes: mockAvailableRecipes,
            },
            global: {
                stubs,
            },
        });

        // Find the cancel button
        const cancelButton = wrapper.findAll('button').find((btn) => btn.text() === 'Cancel');
        expect(cancelButton).toBeDefined();

        // Click the cancel button
        await cancelButton?.trigger('click');

        // Check that the update:open event was emitted with false
        expect(wrapper.emitted('update:open')).toBeTruthy();
        expect(wrapper.emitted('update:open')?.[0]).toEqual([false]);
    });

    it('submits the form with correct data when Add Meal button is clicked', async () => {
        const wrapper = mount(AddMealAssignmentModal, {
            props: {
                open: true,
                mealPlanDay: mockDay,
                availableRecipes: mockAvailableRecipes,
            },
            global: {
                stubs,
            },
        });

        // Find the form and submit it
        const form = wrapper.find('form');
        await form.trigger('submit.prevent');

        // Check that post was called on the shared mockForm instance
        expect(mockForm.post).toHaveBeenCalledOnce();
        expect(mockForm.post).toHaveBeenCalledWith(
            expect.stringContaining('meal-assignments.store'),
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            }),
        );
    });
});
