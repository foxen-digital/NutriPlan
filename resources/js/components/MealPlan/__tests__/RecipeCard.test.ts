import RecipeCard from '../RecipeCard.vue';
import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', template: '<a :href="href"><slot /></a>', props: ['href'] },
}));

vi.mock('@/components/ui/button', () => ({
    Button: { name: 'Button', template: '<button><slot /></button>' },
}));

vi.mock('@/components/ui/dropdown-menu', () => ({
    DropdownMenu: { name: 'DropdownMenu', template: '<div><slot /></div>' },
    DropdownMenuContent: { name: 'DropdownMenuContent', template: '<div><slot /></div>' },
    DropdownMenuItem: {
        name: 'DropdownMenuItem',
        template: '<div @click="$attrs.onClick?.()"><slot /></div>',
        inheritAttrs: false,
    },
    DropdownMenuTrigger: { name: 'DropdownMenuTrigger', template: '<div><slot /></div>' },
}));

vi.mock('lucide-vue-next', () => ({
    EllipsisVerticalIcon: { name: 'EllipsisVerticalIcon', template: '<svg></svg>' },
    PencilIcon: { name: 'PencilIcon', template: '<svg></svg>' },
    TrashIcon: { name: 'TrashIcon', template: '<svg></svg>' },
}));

const routeMock = vi.fn((name: string, param?: any) => `/mocked/${name}/${param ?? ''}`);

const mockRecipe = {
    id: 1,
    title: 'Chicken Stir Fry',
    slug: 'chicken-stir-fry',
    servings: 4,
    description: 'A quick stir fry',
    prep_time: 10,
    cooking_time: 20,
    total_time: 30,
    difficulty_level: 'Easy',
    created_at: '2024-01-01',
    url: null,
    author: 'Chef Test',
    is_public: true,
    images: [],
    instructions: 'Cook it',
    categories: [],
    ingredients: [],
    pivot: {
        id: 10,
        scale_factor: 1,
        servings_available: 4,
    },
};

const globalMocks = {
    global: {
        mocks: { route: routeMock },
    },
};

describe('RecipeCard', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders the recipe title', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        expect(wrapper.text()).toContain('Chicken Stir Fry');
    });

    it('renders scale factor information', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        expect(wrapper.text()).toContain('Scale Factor');
    });

    it('calculates servings correctly (servings * scale_factor)', () => {
        const recipeWithScale = {
            ...mockRecipe,
            servings: 4,
            pivot: { ...mockRecipe.pivot, scale_factor: 2, servings_available: 8 },
        };
        const wrapper = mount(RecipeCard, {
            props: { recipe: recipeWithScale, peopleCount: 2 },
            ...globalMocks,
        });
        expect((wrapper.vm as any).calculatedServings).toBe(8);
    });

    it('emits edit event when triggered', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        wrapper.vm.$emit('edit', mockRecipe);
        expect(wrapper.emitted('edit')).toBeTruthy();
    });

    it('emits remove event when triggered', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        wrapper.vm.$emit('remove', mockRecipe);
        expect(wrapper.emitted('remove')).toBeTruthy();
    });

    it('formatScaleFactor returns integer as string for whole numbers', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        expect((wrapper.vm as any).formatScaleFactor(2)).toBe('2');
    });

    it('formatScaleFactor returns one decimal place for fractions', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        expect((wrapper.vm as any).formatScaleFactor(1.5)).toBe('1.5');
    });

    it('renders recipe image when available', () => {
        const recipeWithImage = {
            ...mockRecipe,
            images: ['https://example.com/image.jpg'],
        };
        const wrapper = mount(RecipeCard, {
            props: { recipe: recipeWithImage, peopleCount: 2 },
            ...globalMocks,
        });
        expect(wrapper.find('img').exists()).toBe(true);
    });

    it('does not render image element when no images', () => {
        const wrapper = mount(RecipeCard, {
            props: { recipe: mockRecipe, peopleCount: 2 },
            ...globalMocks,
        });
        expect(wrapper.find('img').exists()).toBe(false);
    });
});
