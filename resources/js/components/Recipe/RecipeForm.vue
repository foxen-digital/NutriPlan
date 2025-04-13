<template>
    <form @submit.prevent="submit" class="space-y-8">
        <div class="space-y-6">
            <!-- Title -->
            <div>
                <Label for="title">Title</Label>
                <Input id="title" v-model="form.title" type="text" class="mt-1" required />
                <InputError :message="form.errors.title" />
            </div>

            <!-- Description -->
            <div>
                <Label for="description">Description</Label>
                <Textarea id="description" v-model="form.description" class="mt-1 p-3" />
                <InputError :message="form.errors.description" />
            </div>

            <!-- Times and Servings -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div>
                    <Label for="prep_time">Prep Time (minutes)</Label>
                    <Input id="prep_time" v-model.number="form.prep_time" type="number" min="1" class="mt-1" required />
                    <InputError :message="form.errors.prep_time" />
                </div>

                <div>
                    <Label for="cooking_time">Cooking Time (minutes)</Label>
                    <Input id="cooking_time" v-model.number="form.cooking_time" type="number" min="1" class="mt-1" required />
                    <InputError :message="form.errors.cooking_time" />
                </div>

                <div>
                    <Label for="servings">Servings</Label>
                    <Input id="servings" v-model.number="form.servings" type="number" min="1" class="mt-1" required />
                    <InputError :message="form.errors.servings" />
                </div>
            </div>

            <!-- Visibility Toggle -->
            <div class="flex items-center space-x-2">
                <Checkbox id="is_public" :checked="form.is_public" @update:checked="form.is_public = $event" />
                <Label for="is_public" class="cursor-pointer">Make this recipe public</Label>
                <div class="ml-2">
                    <Badge v-if="form.is_public" variant="outline" class="border-green-300 bg-green-100 text-green-800"> Public</Badge>
                    <Badge v-else variant="outline" class="border-gray-300 bg-gray-100 text-gray-800">Private</Badge>
                </div>
                <InputError :message="form.errors.is_public" />
            </div>
            <div v-if="form.is_public && hasSourceUrl" class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-600">
                <span class="font-medium">Note:</span> This is an imported recipe. When made public, other users will only see basic details and will
                be directed to the original source for full recipe instructions.
            </div>

            <!-- Categories -->
            <div>
                <Label>Categories</Label>
                <div class="mt-1 flex flex-wrap gap-2">
                    <Badge
                        v-for="category in selectedCategories"
                        :key="category.id"
                        variant="outline"
                        class="cursor-pointer bg-primary text-primary-foreground hover:bg-primary/90"
                        @click="toggleCategory(category.id)"
                    >
                        {{ category.name }}
                        <XIcon class="ml-1 h-3 w-3" />
                    </Badge>
                </div>

                <div class="combobox-with-create mt-2">
                    <ComboboxWithCreate
                        :model-value="0"
                        :options="availableCategories"
                        placeholder="Search for categories..."
                        :allow-create="true"
                        create-endpoint="/categories"
                        @update:model-value="addCategory"
                        @option-created="handleNewCategory"
                    />
                </div>
                <InputError :message="form.errors.categories" />
            </div>

            <!-- Ingredients -->
            <div>
                <Label>Ingredients</Label>
                <div class="mt-4 space-y-4">
                    <div v-for="(ingredient, index) in form.ingredients" :key="index" class="flex items-end gap-4">
                        <div class="flex-1">
                            <Label :for="'ingredient-' + index">Ingredient</Label>
                            <ComboboxWithCreate
                                :id="'ingredient-' + index"
                                v-model="ingredient.ingredient_id"
                                :options="ingredients"
                                :selected="ingredients.find((i) => i.id === ingredient.ingredient_id)"
                                :allow-create="true"
                                create-endpoint="/ingredients"
                                class="mt-1"
                                @option-created="handleNewIngredient"
                            />
                            <InputError :message="form.errors['ingredients.' + index + '.ingredient_id']" />
                        </div>

                        <div class="w-24">
                            <Label :for="'amount-' + index">Amount</Label>
                            <Input :id="'amount-' + index" v-model.number="ingredient.amount" type="number" min="0" step="0.01" class="mt-1" />
                            <InputError :message="form.errors['ingredients.' + index + '.amount']" />
                        </div>

                        <div class="w-32">
                            <Label :for="'unit-' + index">Unit</Label>
                            <Select :id="'unit-' + index" v-model="ingredient.unit" :options="measurementUnits" class="mt-1" :allow-empty="true" />
                            <InputError :message="form.errors['ingredients.' + index + '.unit']" />
                        </div>

                        <Button type="button" variant="destructive" size="icon" @click="removeIngredient(index)">
                            <TrashIcon class="h-4 w-4" />
                        </Button>
                    </div>

                    <Button type="button" variant="outline" @click="addIngredient">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Add Ingredient
                    </Button>
                </div>
            </div>

            <!-- Instructions -->
            <div>
                <Label for="instructions">Instructions</Label>
                <Textarea id="instructions" v-model="form.instructions" class="mt-1 p-3" required rows="10" />
                <InputError :message="form.errors.instructions" />
            </div>

            <!-- Images -->
            <div>
                <Label>Images</Label>
                <div class="mt-1">
                    <FileInput v-model="form.images" multiple accept="image/*" />
                </div>
                <InputError :message="form.errors.images" />
            </div>

            <!-- Nutrition Information -->
            <NutritionForm v-model="form.nutrition_information" />
        </div>

        <div class="flex justify-end gap-4">
            <Button type="button" variant="outline" @click="() => form.get(route('recipes.index'))">Cancel</Button>
            <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import NutritionForm from '@/components/Recipe/NutritionForm.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ComboboxWithCreate } from '@/components/ui/combobox';
import { FileInput } from '@/components/ui/file-input';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { MeasurementUnit, Recipe } from '@/types/recipe';
import type { FormDataConvertible } from '@inertiajs/core';
import { useForm } from '@inertiajs/vue3';
import { PlusIcon, TrashIcon, XIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        recipe?: Recipe;
        submitLabel?: string;
        categories: Array<{ id: number; name: string }>;
        ingredients: Array<{ id: number; name: string }>;
        measurementUnits: MeasurementUnit[];
    }>(),
    {
        submitLabel: 'Save Recipe',
    },
);

interface FormData {
    title: string;
    description: string;
    instructions: string;
    prep_time: number;
    cooking_time: number;
    servings: number;
    is_public: boolean;
    categories: number[];
    ingredients: Array<{
        ingredient_id: number;
        amount: number;
        unit: string | null;
    }>;
    images: File[];
    nutrition_information: {
        calories?: string;
        carbohydrate_content?: string;
        cholesterol_content?: string;
        fat_content?: string;
        fiber_content?: string;
        protein_content?: string;
        saturated_fat_content?: string;
        serving_size?: string;
        sodium_content?: string;
        sugar_content?: string;
        trans_fat_content?: string;
        unsaturated_fat_content?: string;
    };
    [key: string]: FormDataConvertible;
}

const form = useForm<FormData>({
    title: props.recipe?.title ?? '',
    description: props.recipe?.description ?? '',
    instructions: props.recipe?.instructions ?? '',
    prep_time: props.recipe?.prep_time ?? 30,
    cooking_time: props.recipe?.cooking_time ?? 30,
    servings: props.recipe?.servings ?? 4,
    is_public: props.recipe?.is_public ?? false,
    categories: props.recipe?.categories?.map((c) => c.id) ?? [],
    ingredients:
        props.recipe?.ingredients?.map((i) => ({
            ingredient_id: i.id,
            amount: i.pivot.amount,
            unit: i.pivot.unit,
        })) ?? [],
    images: [] as File[],
    nutrition_information: props.recipe?.nutrition_information ?? {},
});

const hasSourceUrl = computed(() => props.recipe?.url);

const toggleCategory = (id: number) => {
    const index = form.categories.indexOf(id);
    if (index !== -1) {
        form.categories.splice(index, 1);
    }
};

const addCategory = (id: number) => {
    if (id === 0) return; // Skip if no category is selected
    if (!form.categories.includes(id)) {
        form.categories.push(id);
    }
};

const addIngredient = () => {
    form.ingredients.push({
        ingredient_id: 0,
        amount: 1,
        unit: '',
    });
};

const removeIngredient = (index: number) => {
    form.ingredients.splice(index, 1);
};

const emit = defineEmits<{
    (e: 'submit', form: ReturnType<typeof useForm<FormData>>): void;
    (e: 'new-ingredient', ingredient: { id: number; name: string }): void;
    (e: 'new-category', category: { id: number; name: string }): void;
}>();

const handleNewIngredient = (newIngredient: { id: number; name: string }) => {
    // Emit event to add the new ingredient to the ingredients list
    emit('new-ingredient', newIngredient);
};

const handleNewCategory = (newCategory: { id: number; name: string }) => {
    // Emit event to add the new category to the categories list
    emit('new-category', newCategory);
};

const submit = () => {
    emit('submit', form);
};

const selectedCategories = computed(() => {
    return props.categories.filter((category) => form.categories.includes(category.id));
});

const availableCategories = computed(() => {
    return props.categories.filter((category) => !form.categories.includes(category.id));
});
</script>
