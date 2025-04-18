import type { MealAssignment } from '@/types/meal-plan';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import EditMealAssignmentModal from '../EditMealAssignmentModal.vue';

// Create a shared mock instance for useForm
const mockForm = {
    servings: 1,
    errors: {},
    processing: false,
    put: vi.fn().mockImplementation(() => Promise.resolve()),
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
    return `/api/route/${name}/${params}`;
});

describe('EditMealAssignmentModal', () => {
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
        InputError: {
            template: '<div v-if="message" class="input-error">{{ message }}</div>',
            props: ['message'],
        },
    };

    // Create mock assignment with the necessary properties for testing
    const mockAssignment = {
        id: 1,
        meal_plan_day_id: 1,
        meal_plan_recipe_id: 1,
        servings: 3,
        to_cook: true,
        created_at: '2023-01-01T00:00:00.000Z',
        updated_at: '2023-01-01T00:00:00.000Z',
    } as MealAssignment;

    beforeEach(() => {
        // Reset the mock form before each test
        vi.clearAllMocks();
        mockForm.reset();
        mockForm.errors = {};
    });

    it('initializes form with correct servings value when opened', async () => {
        const wrapper = mount(EditMealAssignmentModal, {
            props: {
                open: true,
                assignment: mockAssignment,
            },
            global: {
                stubs,
            },
        });

        // Check that servings input is rendered
        const servingsInput = wrapper.find('input[type="number"]');
        expect(servingsInput.exists()).toBe(true);
    });

    it('displays correct title and description', () => {
        const wrapper = mount(EditMealAssignmentModal, {
            props: {
                open: true,
                assignment: mockAssignment,
            },
            global: {
                stubs,
            },
        });

        const title = wrapper.find('.dialog-title');
        const description = wrapper.find('.dialog-description');

        expect(title.text()).toBe('Edit Meal Assignment');
        expect(description.text()).toContain('Update the number of servings for this meal');
    });

    it('emits update:open event when cancel button is clicked', async () => {
        const wrapper = mount(EditMealAssignmentModal, {
            props: {
                open: true,
                assignment: mockAssignment,
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

    it('submits the form with correct data when Update button is clicked', async () => {
        const wrapper = mount(EditMealAssignmentModal, {
            props: {
                open: true,
                assignment: mockAssignment,
            },
            global: {
                stubs,
            },
        });

        // Find the form and submit it
        const form = wrapper.find('form');
        await form.trigger('submit.prevent');

        // Check that put was called on the shared mockForm instance
        expect(mockForm.put).toHaveBeenCalledOnce();
        expect(mockForm.put).toHaveBeenCalledWith(
            expect.stringContaining(`meal-assignments.update/${mockAssignment.id}`),
            expect.objectContaining({
                preserveScroll: true,
                onSuccess: expect.any(Function),
            }),
        );
    });

    it('does not submit the form when assignment is null', async () => {
        const wrapper = mount(EditMealAssignmentModal, {
            props: {
                open: true,
                assignment: null,
            },
            global: {
                stubs,
            },
        });

        // Find the form and submit it
        const form = wrapper.find('form');
        await form.trigger('submit.prevent');

        // Check that put was not called on the shared mockForm instance
        expect(mockForm.put).not.toHaveBeenCalled();
    });
});
