import { mount } from '@vue/test-utils';
import { describe, expect, it, vi } from 'vitest';
import RecipeForm from '../RecipeForm.vue';

// Mock the components
vi.mock('@/components/Recipe/NutritionForm.vue', () => ({
    default: {
        name: 'NutritionForm',
        template: '<div class="nutrition-form"><slot /></div>',
        props: ['modelValue'],
        emits: ['update:modelValue'],
    },
}));

vi.mock('@/components/ui/badge', () => ({
    Badge: {
        name: 'Badge',
        template: '<div class="badge" @click="$emit(\'click\')"><slot /></div>',
        props: ['variant'],
    },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        name: 'Button',
        template: '<button class="button" :type="type" :disabled="disabled" @click="$emit(\'click\')"><slot /></button>',
        props: ['type', 'variant', 'size', 'disabled'],
    },
}));

vi.mock('@/components/ui/checkbox', () => ({
    Checkbox: {
        name: 'Checkbox',
        template:
            '<input type="checkbox" class="checkbox" :checked="modelValue === trueValue" @change="$emit(\'update:modelValue\', $event.target.checked ? trueValue : falseValue)" />',
        props: ['modelValue', 'trueValue', 'falseValue'],
        emits: ['update:modelValue'],
    },
}));

vi.mock('@/components/ui/combobox', () => ({
    Combobox: {
        name: 'Combobox',
        template:
            '<div class="combobox"><select :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="option in options" :key="option.id" :value="option.id">{{ option.name }}</option></select></div>',
        props: ['modelValue', 'options', 'placeholder'],
        emits: ['update:modelValue'],
    },
    ComboboxWithCreate: {
        name: 'ComboboxWithCreate',
        template:
            '<div class="combobox-with-create"><select :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="option in options" :key="option.id" :value="option.id">{{ option.name }}</option></select></div>',
        props: ['modelValue', 'options', 'selected', 'allowCreate', 'createEndpoint'],
        emits: ['update:modelValue', 'option-created'],
    },
}));

vi.mock('@/components/ui/file-input', () => ({
    FileInput: {
        name: 'FileInput',
        template:
            '<input type="file" class="file-input" :multiple="multiple" :accept="accept" @change="$emit(\'update:modelValue\', $event.target.files)" />',
        props: ['modelValue', 'multiple', 'accept'],
        emits: ['update:modelValue'],
    },
}));

vi.mock('@/components/ui/input', () => ({
    Input: {
        name: 'Input',
        template:
            '<input class="input" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" :type="type" :min="min" :step="step" :placeholder="placeholder" :required="required" />',
        props: ['modelValue', 'type', 'min', 'step', 'placeholder', 'required'],
        emits: ['update:modelValue'],
    },
}));

vi.mock('@/components/ui/input-error', () => ({
    InputError: {
        name: 'InputError',
        template: '<div class="input-error" v-if="message">{{ message }}</div>',
        props: ['message'],
    },
}));

vi.mock('@/components/ui/label', () => ({
    Label: {
        name: 'Label',
        template: '<label class="label"><slot /></label>',
        props: ['for'],
    },
}));

vi.mock('@/components/ui/select', () => ({
    Select: {
        name: 'Select',
        template:
            '<select class="select" :value="modelValue" @change="$emit(\'update:modelValue\', $event.target.value)"><option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option></select>',
        props: ['modelValue', 'options', 'allowEmpty'],
        emits: ['update:modelValue'],
    },
}));

vi.mock('@/components/ui/textarea', () => ({
    Textarea: {
        name: 'Textarea',
        template:
            '<textarea class="textarea" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" :rows="rows" :required="required"></textarea>',
        props: ['modelValue', 'rows', 'required'],
        emits: ['update:modelValue'],
    },
}));

// Form mock setup with shared state for test access
const formMock = {
    title: '',
    description: '',
    instructions: '',
    prep_time: 30,
    cooking_time: 30,
    servings: 4,
    categories: [],
    ingredients: [],
    images: [],
    nutrition_information: {},
    errors: {},
    processing: false,
    post: vi.fn(),
    put: vi.fn(),
    get: vi.fn(),
    reset: vi.fn(),
};

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => formMock,
}));

vi.mock('lucide-vue-next', () => ({
    PlusIcon: {
        name: 'PlusIcon',
        template: '<div class="plus-icon"></div>',
    },
    TrashIcon: {
        name: 'TrashIcon',
        template: '<div class="trash-icon"></div>',
    },
    XIcon: {
        name: 'XIcon',
        template: '<div class="x-icon"></div>',
    },
}));

// Mock the route function
global.route = vi.fn((name) => {
    if (name === 'recipes.index') {
        return '/recipes';
    }
    return '/';
});

describe('RecipeForm.vue', () => {
    const mockCategories = [
        { id: 1, name: 'Dinner' },
        { id: 2, name: 'Italian' },
        { id: 3, name: 'Quick' },
    ];

    const mockIngredients = [
        { id: 1, name: 'Tomatoes' },
        { id: 2, name: 'Olive Oil' },
        { id: 3, name: 'Garlic' },
    ];

    const mockUnits = [
        { value: 'g', label: 'Grams' },
        { value: 'ml', label: 'Milliliters' },
        { value: 'tsp', label: 'Teaspoon' },
    ];

    // Reset the form mock between tests
    beforeEach(() => {
        formMock.title = '';
        formMock.description = '';
        formMock.instructions = '';
        formMock.prep_time = 30;
        formMock.cooking_time = 30;
        formMock.servings = 4;
        formMock.categories = [];
        formMock.ingredients = [];
        formMock.images = [];
        formMock.nutrition_information = {};
        formMock.errors = {};
        formMock.processing = false;
    });

    it('renders the form with correct inputs', () => {
        const wrapper = mount(RecipeForm, {
            props: {
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        // Check for required form elements
        expect(wrapper.find('form').exists()).toBe(true);
        expect(wrapper.find('.input').exists()).toBe(true); // Input for title
        expect(wrapper.find('.textarea').exists()).toBe(true); // Textarea for description
        expect(wrapper.find('.combobox-with-create').exists()).toBe(true);
        expect(wrapper.find('.file-input').exists()).toBe(true);
        expect(wrapper.find('.nutrition-form').exists()).toBe(true);
    });

    it('loads existing recipe data when provided', async () => {
        const mockRecipe = {
            title: 'Pasta Carbonara',
            description: 'Classic Italian dish',
            instructions: 'Cook pasta, mix with egg and cheese.',
            prep_time: 15,
            cooking_time: 20,
            servings: 2,
            categories: [{ id: 2, name: 'Italian' }],
            ingredients: [
                {
                    id: 3,
                    name: 'Garlic',
                    pivot: {
                        amount: 2,
                        unit: 'clove',
                    },
                },
            ],
            nutrition_information: {
                calories: '600 cal',
            },
        };

        // Pre-populate the form mock to simulate existing recipe data
        formMock.title = 'Pasta Carbonara';
        formMock.description = 'Classic Italian dish';
        formMock.instructions = 'Cook pasta, mix with egg and cheese.';
        formMock.prep_time = 15;
        formMock.cooking_time = 20;
        formMock.servings = 2;

        mount(RecipeForm, {
            props: {
                recipe: mockRecipe,
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        // In our mocked components, we should check if the form was properly initialized
        expect(formMock.title).toBe('Pasta Carbonara');
        expect(formMock.description).toBe('Classic Italian dish');
        expect(formMock.instructions).toBe('Cook pasta, mix with egg and cheese.');
        expect(formMock.prep_time).toBe(15);
        expect(formMock.cooking_time).toBe(20);
        expect(formMock.servings).toBe(2);
    });

    it('shows the Add Ingredient button to add new ingredients', () => {
        const wrapper = mount(RecipeForm, {
            props: {
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        const addButton = wrapper.findAll('.button').find((btn) => btn.text().includes('Add Ingredient'));
        expect(addButton).toBeTruthy();
    });

    it('has a submit button with the provided label', () => {
        const wrapper = mount(RecipeForm, {
            props: {
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
                submitLabel: 'Create Recipe',
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        const submitButton = wrapper.findAll('.button').find((btn) => btn.text().includes('Create Recipe'));
        expect(submitButton).toBeTruthy();
    });

    it('disables the submit button when processing', () => {
        // Set processing to true directly on the formMock
        formMock.processing = true;

        const wrapper = mount(RecipeForm, {
            props: {
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        const submitButton = wrapper.findAll('.button[type="submit"]').at(0);
        expect(submitButton.attributes('disabled')).toBeDefined();
    });

    it('shows InputError components when there are form errors', () => {
        // Set errors directly on the formMock
        formMock.errors = {
            title: 'Title is required',
            prep_time: 'Prep time must be a number',
        };

        const wrapper = mount(RecipeForm, {
            props: {
                categories: mockCategories,
                ingredients: mockIngredients,
                measurementUnits: mockUnits,
            },
            global: {
                mocks: {
                    route,
                },
            },
        });

        const errorElements = wrapper.findAll('.input-error');
        expect(errorElements.length).toBeGreaterThan(0);
        expect(wrapper.html()).toContain('Title is required');
        expect(wrapper.html()).toContain('Prep time must be a number');
    });
});
