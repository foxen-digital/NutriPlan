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
                <div class="mt-4 hidden flex-wrap items-center gap-2 sm:mt-0 sm:flex">
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

                    <!-- Cook Mode Toggle (Desktop) -->
                    <div v-if="isWakeLockSupported" class="flex items-center gap-2" data-test="cook-mode-toggle">
                        <Switch v-model:checked="isCookModeEnabled" @update:checked="toggleCookMode" data-test="cook-mode-switch" />
                        <span class="text-sm text-gray-700 dark:text-gray-300"> Keep Screen Awake </span>
                    </div>
                </div>
            </div>

            <!-- Floating Action Button for mobile -->
            <div class="fixed right-4 top-4 z-10 flex flex-col-reverse gap-2 sm:hidden">
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button size="icon" class="h-12 w-12 rounded-full">
                            <MenuIcon class="h-6 w-6" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem v-if="isOwner" @click="navigateToEdit">
                            <PencilIcon class="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="toggleFavorite">
                            <HeartIcon :class="['mr-2 h-4 w-4', { 'fill-current': isFavorited }]" />
                            {{ isFavorited ? 'Favorited' : 'Add to Favorites' }}
                        </DropdownMenuItem>
                        <DropdownMenuItem v-if="mealPlans.length > 0 && !hideDetails" @click="showMealPlanMenu = true">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add to Meal Plan
                        </DropdownMenuItem>
                        <!-- Cook Mode Toggle (Mobile) -->
                        <DropdownMenuItem v-if="isWakeLockSupported" @click="toggleCookMode" data-test="cook-mode-toggle-mobile">
                            <CoffeeIcon :class="['mr-2 h-4 w-4', { 'fill-current': isCookModeEnabled }]" />
                            {{ isCookModeEnabled ? 'Disable Cook Mode' : 'Enable Cook Mode' }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Second dropdown for meal plans -->
                <DropdownMenu v-if="mealPlans.length > 0 && !hideDetails" v-model:open="showMealPlanMenu">
                    <DropdownMenuContent align="end" class="w-56">
                        <DropdownMenuLabel>Select a Meal Plan</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem v-for="plan in mealPlans" :key="plan.id" @click="addToMealPlan(plan.id)">
                            {{ plan.name || formatDate(plan.start_date) }}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Cook Mode Indicator (Mobile) -->
                <div
                    v-if="isWakeLockSupported && isCookModeEnabled"
                    class="fixed bottom-4 left-4 z-10 rounded-full bg-amber-500 p-2 text-white shadow-lg"
                    data-test="cook-mode-indicator"
                >
                    <CoffeeIcon class="h-6 w-6" />
                </div>
            </div>

            <div class="mt-8 overflow-hidden bg-white p-6 shadow-xl dark:bg-gray-800 sm:rounded-lg">
                <div class="flex flex-col gap-8 md:flex-row">
                    <!-- Image carousel - now displayed at the top on mobile -->
                    <div v-if="recipe.images && recipe.images.length > 0" class="h-80 md:order-2 md:w-1/3">
                        <Carousel :images="recipe.images" :autoplay="true" :interval="5000" />
                    </div>

                    <!-- Main content column -->
                    <div class="flex-1 md:order-1">
                        <!-- Description -->
                        <p v-if="recipe.description" class="text-gray-600 dark:text-gray-300">
                            {{ recipe.description }}
                        </p>

                        <!-- Details - Compact mobile version with icons -->
                        <div class="mt-6">
                            <!-- Mobile version with icons -->
                            <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50 md:hidden">
                                <div class="flex items-center gap-2">
                                    <ClockIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Prep</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ recipe.prep_time }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UtensilsIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Cook</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ recipe.cooking_time }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UsersIcon class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Servings</p>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ recipe.servings }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop version (original cards) -->
                            <div class="hidden grid-cols-3 gap-4 md:grid">
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

                        <!-- Nutrition Information -->
                        <div v-if="recipe.nutrition_information" class="mt-8">
                            <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Nutrition</h2>
                            <NutritionInformation :nutrition="recipe.nutrition_information" />
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
                                <template v-if="isInstructionsMarkdown">
                                    <div class="prose prose-gray max-w-none dark:prose-invert">
                                        <VueMarkdownRender :source="recipe.instructions" />
                                    </div>
                                </template>
                                <template v-else>
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
                                </template>
                            </div>
                        </template>
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
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Recipe } from '@/types/recipe';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ClockIcon, CoffeeIcon, ExternalLinkIcon, HeartIcon, MenuIcon, PencilIcon, PlusIcon, UsersIcon, UtensilsIcon } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import VueMarkdownRender from 'vue-markdown-render';

// TypeScript interface for WakeLockSentinel
interface WakeLockSentinel {
    released: boolean;
    release(): Promise<void>;
    addEventListener(type: string, listener: EventListenerOrEventListenerObject): void;
    removeEventListener(type: string, listener: EventListenerOrEventListenerObject): void;
}

// TypeScript interface for WakeLock API
interface WakeLock {
    request(type: 'screen'): Promise<WakeLockSentinel>;
}

// Extend the Navigator interface to include wakeLock
declare global {
    interface Navigator {
        wakeLock?: WakeLock;
    }
}

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
const showMealPlanMenu = ref(false);

// Cook Mode / Wake Lock functionality
const isWakeLockSupported = ref(false);
const isCookModeEnabled = ref(false);
const wakeLock = ref<WakeLockSentinel | null>(null);

// Check if wake lock API is supported
onMounted(() => {
    // Check if the browser supports the Wake Lock API
    isWakeLockSupported.value =
        true ||
        ('wakeLock' in navigator &&
            // Additional check to focus on mobile/tablet devices (optional)
            (window.innerWidth <= 1024 || 'ontouchstart' in window));

    // Set up visibility change event listener
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

// Clean up event listeners and release wake lock on component unmount
onBeforeUnmount(() => {
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    releaseWakeLock();
});

// Toggle cook mode and handle wake lock
const toggleCookMode = async (state?: boolean) => {
    // If state is explicitly provided, use it, otherwise toggle the current state
    const newState = state !== undefined ? state : !isCookModeEnabled.value;

    if (newState) {
        try {
            // Request a screen wake lock
            if (navigator.wakeLock) {
                wakeLock.value = await navigator.wakeLock.request('screen');

                // Set the state to enabled if successful
                isCookModeEnabled.value = true;

                // Add released event listener to handle external wake lock releases
                const handleRelease = () => {
                    // Only update UI if the component is still mounted
                    isCookModeEnabled.value = false;
                    wakeLock.value = null;
                };

                wakeLock.value.addEventListener('release', handleRelease);

                console.log('Wake lock enabled');
            }
        } catch (error) {
            // Handle errors (e.g., permission denied, unsupported, etc.)
            console.error('Failed to enable wake lock:', error);
            isCookModeEnabled.value = false;
        }
    } else {
        // Disable cook mode by releasing the wake lock
        releaseWakeLock();
    }
};

// Release wake lock if it exists
const releaseWakeLock = async () => {
    if (wakeLock.value) {
        try {
            await wakeLock.value.release();
            wakeLock.value = null;
        } catch (error) {
            console.error('Failed to release wake lock:', error);
        } finally {
            isCookModeEnabled.value = false;
        }
    }
};

// Handle visibility changes (e.g., user switches tabs or apps)
const handleVisibilityChange = async () => {
    // If the page becomes visible again and cook mode was enabled
    if (document.visibilityState === 'visible' && isCookModeEnabled.value && !wakeLock.value && navigator.wakeLock) {
        // Re-request the wake lock
        try {
            wakeLock.value = await navigator.wakeLock.request('screen');
            console.log('Wake lock re-acquired after visibility change');
        } catch (error) {
            console.error('Failed to re-acquire wake lock:', error);
            isCookModeEnabled.value = false;
        }
    }
};

// Check if the text is markdown
const isMarkdown = (text: string): boolean => {
    // Simple check for common markdown syntax
    const markdownPatterns = [
        /^#+ .+$/m, // Headers
        /\*\*.+\*\*/, // Bold
        /\*.+\*/, // Italic
        /\[.+\]\(.+\)/, // Links
        /^\s*[\*\-\+] .+/m, // Unordered lists
        /^\s*\d+\. .+/m, // Ordered lists
        /```[\s\S]*```/, // Code blocks
    ];

    return markdownPatterns.some((pattern) => pattern.test(text));
};

const isInstructionsMarkdown = computed(() => {
    return isMarkdown(props.recipe.instructions);
});

const parseInstructions = (instructions: string): string[] => {
    // If it's not markdown, use the existing implementation
    if (!isInstructionsMarkdown.value) {
        return instructions.split('\n').filter((line) => line.trim());
    }

    // For markdown, we'll return the whole string as a single item
    // since we'll render it with the markdown component
    return [instructions];
};

// Scaling functionality
const scalingFactor = ref(1.0);

const updateScalingFactor = (factor: number) => {
    scalingFactor.value = factor;
};

const formatScaledAmount = (amount: number): string => {
    if (!amount) return '';

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

const navigateToEdit = () => {
    router.visit(route('recipes.edit', props.recipe.slug));
};
</script>
