<template>
    <AppLayout>
        <Head :title="`${recipe.title} | NutriPlan`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">{{ recipe.title }}</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                        Created by
                        <Link
                            v-if="recipe.user.slug"
                            :href="route('recipes.by-user', { user: recipe.user.slug })"
                            class="text-blue-600 hover:underline dark:text-blue-400"
                        >
                            {{ recipe.user.name }}
                        </Link>
                        <span v-else>{{ recipe.user.name }}</span>
                        on {{ new Date(recipe.created_at).toLocaleDateString() }}
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <Badge v-if="recipe.is_public" variant="outline" class="border-green-300 bg-green-100 text-green-800">Public</Badge>
                        <Badge v-else variant="outline" class="border-gray-300 bg-gray-100 text-gray-800">Private </Badge>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-2 sm:mt-0">
                    <Link v-if="isOwner" :href="route('recipes.edit', recipe.slug)">
                        <Button variant="outline" size="sm">
                            <PencilIcon class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>

                    <Button size="sm" @click="toggleFavorite" :variant="isFavorited ? 'default' : 'outline'">
                        <HeartIcon :class="['mr-2 h-4 w-4', { 'fill-current': isFavorited }]" />
                        {{ isFavorited ? 'Favorited' : 'Add to Favorites' }}
                    </Button>

                    <DropdownMenu v-if="mealPlans.length > 0 && !hideDetails">
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="sm">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Add to Meal Plan
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel>Select a Meal Plan</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem v-for="plan in mealPlans" :key="plan.id" @click="addToMealPlan(plan.id)">
                                {{ plan.name || formatDate(plan.start_date) }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="mt-8 overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="flex flex-col gap-8 md:flex-row">
                    <!-- Main content column -->
                    <div class="flex-1">
                        <!-- Description -->
                        <p v-if="recipe.description" class="text-gray-600 dark:text-gray-300">
                            {{ recipe.description }}
                        </p>

                        <!-- Details -->
                        <div class="mt-6">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Prep Time</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ recipe.prep_time }} minutes</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Cooking Time</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ recipe.cooking_time }} minutes</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Servings</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ recipe.servings }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Nutrition Information -->
                        <div v-if="recipe.nutrition_information" class="mt-8">
                            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Nutrition</h2>
                            <NutritionInformation :nutrition="recipe.nutrition_information" />
                        </div>

                        <!-- Categories -->
                        <div v-if="recipe.categories.length > 0" class="mt-6">
                            <div class="flex flex-wrap gap-2">
                                <Link v-for="category in recipe.categories" :key="category.id" :href="route('categories.show', category.slug)">
                                    <Badge variant="secondary" class="cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-700">
                                        {{ category.name }}
                                    </Badge>
                                </Link>
                            </div>
                        </div>

                        <!-- Source Attribution -->
                        <div v-if="recipe.author || recipe.url" class="mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Source</h2>
                            <p class="text-gray-600 dark:text-gray-300">
                                <span v-if="recipe.author">{{ recipe.author }}:&nbsp;</span>
                                <a
                                    v-if="recipe.url"
                                    :href="recipe.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    {{ recipe.url }}
                                </a>
                            </p>
                        </div>

                        <!-- All content below is hidden for non-owners viewing imported public recipes -->
                        <template v-if="!hideDetails">
                            <!-- Scaling Control -->
                            <div class="mt-6">
                                <ScalingControl :original-servings="recipe.servings" @update:scaling-factor="updateScalingFactor" />
                            </div>

                            <!-- Ingredients -->
                            <div class="mt-8">
                                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Ingredients</h2>
                                <ul class="space-y-2">
                                    <li
                                        v-for="ingredient in recipe.ingredients"
                                        :key="ingredient.id"
                                        class="flex items-center text-gray-700 dark:text-gray-300"
                                    >
                                        <div class="mr-3 h-1.5 w-1.5 rounded-full bg-gray-600 dark:bg-gray-400" />
                                        <span class="font-medium">
                                            {{ formatScaledAmount(ingredient.pivot.amount) }}
                                            <template v-if="ingredient.pivot.unit">{{ ingredient.pivot.unit }}</template>
                                        </span>
                                        <span class="ml-1">{{ ingredient.pivot.description || ingredient.name }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Instructions -->
                            <div class="mt-8">
                                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Instructions</h2>
                                <ul class="list-none space-y-6">
                                    <li
                                        v-for="(step, index) in parseInstructions(recipe.instructions)"
                                        :key="index"
                                        class="text-gray-700 dark:text-gray-300"
                                    >
                                        <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Step {{ index + 1 }}</h3>
                                        <p>{{ step }}</p>
                                    </li>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <!-- Image carousel column -->
                    <div v-if="recipe.images && recipe.images.length > 0" class="h-80 md:w-1/3">
                        <Carousel :images="recipe.images" :autoplay="true" :interval="5000" />
                    </div>
                </div>
            </div>
            <!-- Original source notice for imported public recipes viewed by non-owners -->
            <div v-if="hideDetails" class="mt-4 rounded-md border-2 border-amber-500 bg-amber-50 p-4">
                <h2 class="text-lg font-semibold text-amber-800">This recipe was imported from another website</h2>
                <p class="mt-2 text-amber-700">The full ingredients and instructions are available at the original source:</p>
                <a
                    v-if="recipe.url"
                    :href="recipe.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-4 inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700"
                >
                    <ExternalLinkIcon class="mr-2 h-4 w-4" /> View Original Recipe
                </a>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import NutritionInformation from '@/components/Recipe/NutritionInformation.vue';
import ScalingControl from '@/components/Recipe/ScalingControl.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import Carousel from '@/components/ui/carousel.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Recipe } from '@/types/recipe';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ExternalLinkIcon, HeartIcon, PencilIcon, PlusIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    recipe: Recipe & { is_favorited?: boolean };
    isOwner: boolean;
    hideDetails: boolean;
    mealPlans: Array<{
        id: number;
        name: string | null;
        start_date: string;
    }>;
}>();

const isFavorited = ref(props.recipe.is_favorited || false);

const parseInstructions = (instructions: string): string[] => {
    return instructions.split('\n').filter((line) => line.trim());
};

// Scaling functionality
const scalingFactor = ref(1.0);

const updateScalingFactor = (factor: number) => {
    scalingFactor.value = factor;
};

const formatScaledAmount = (amount: number): string => {
    if (!amount) return '0';

    const scaled = amount * scalingFactor.value;

    // For small values, show more decimal places
    if (scaled < 0.1) {
        return scaled.toFixed(2);
    }

    // For values less than 1, show one decimal place
    if (scaled < 1) {
        return scaled.toFixed(1);
    }

    // For values with decimal parts, show one decimal place
    if (scaled % 1 !== 0) {
        return scaled.toFixed(1);
    }

    // For whole numbers, show no decimal places
    return scaled.toFixed(0);
};

const toggleFavorite = () => {
    // Use axios with the CSRF token that Laravel automatically includes
    // when using the default Laravel mix/vite setup
    axios
        .post(route('recipes.favorite', props.recipe.slug))
        .then((response: { data: { favorited: boolean } }) => {
            // The controller returns a JSON response with a 'favorited' boolean
            isFavorited.value = response.data.favorited;
        })
        .catch((error: any) => {
            console.error('Failed to toggle favorite:', error);
        });
};

const addToMealPlan = (mealPlanId: number) => {
    axios
        .post(route('meal-plans.add-recipe'), {
            meal_plan_id: mealPlanId,
            recipe_id: props.recipe.id,
            scale_factor: 1.0,
        })
        .then((response) => {
            // Optional success notification
            console.log('Recipe added successfully:', response.data.message);
        })
        .catch((error) => {
            console.error('Error adding recipe to meal plan:', error);
        });
};

// Format date to readable string
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};
</script>
