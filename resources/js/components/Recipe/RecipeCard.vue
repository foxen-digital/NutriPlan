<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { ClockIcon, EllipsisVerticalIcon, FolderPlusIcon, HeartIcon, UsersIcon, UtensilsIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    recipe: {
        id: number;
        title: string;
        description: string | null;
        slug: string;
        prep_time: number;
        cooking_time: number;
        servings: number;
        images: string[];
        url: string | null;
        user: {
            name: string;
            slug?: string;
        };
        categories: {
            id: number;
            name: string;
            slug: string;
            recipe_count: number;
        }[];
        is_favorited?: boolean;
    };
}

interface Collection {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface MealPlan {
    id: number;
    name: string | null;
    start_date: string;
    duration: number;
}

const props = defineProps<Props>();
const isAddToCollectionModalOpen = ref(false);
const isAddToMealPlanModalOpen = ref(false);
const collections = ref<Collection[]>([]);
const mealPlans = ref<MealPlan[]>([]);
const isFavorited = ref(props.recipe.is_favorited || false);

const formatTime = (minutes: number): string => {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (hours === 0) {
        return `${remainingMinutes}m`;
    }

    return remainingMinutes === 0 ? `${hours}h` : `${hours}h ${remainingMinutes}m`;
};

const topCategories = computed(() => {
    if (!props.recipe.categories) return [];
    return [...props.recipe.categories].sort((a, b) => b.recipe_count - a.recipe_count).slice(0, 3);
});

const sitename = computed(() => {
    if (!props.recipe.url) return null;
    try {
        const url = new URL(props.recipe.url);
        return url.hostname.replace(/^www\./, '');
    } catch {
        return null;
    }
});

const form = useForm({
    collection_id: '',
    recipe_id: props.recipe.id,
});

const mealPlanForm = useForm({
    meal_plan_id: '',
    recipe_id: props.recipe.id,
    scale_factor: 1.0,
});

const openAddToCollectionModal = async () => {
    try {
        const response = await fetch(route('collections.index'), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        collections.value = data.collections || [];
        isAddToCollectionModalOpen.value = true;
    } catch (error) {
        console.error('Failed to fetch collections:', error);
    }
};

const openAddToMealPlanModal = async () => {
    try {
        // Use axios instead of fetch, with a query parameter to request JSON
        const response = await axios.get(route('meal-plans.index', { format: 'json' }), {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        // Get today's date in YYYY-MM-DD format for comparison
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Filter meal plans that haven't ended yet
        mealPlans.value = (response.data.mealPlans || []).filter((plan: MealPlan) => {
            // Calculate end date (start_date + duration - 1)
            const startDate = new Date(plan.start_date);
            startDate.setHours(0, 0, 0, 0);

            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + plan.duration - 1);

            // Keep meal plans that end today or in the future
            return endDate >= today;
        });

        isAddToMealPlanModalOpen.value = true;
    } catch (error) {
        console.error('Failed to fetch meal plans:', error);
    }
};

const addToCollection = () => {
    form.post(route('collections.add-recipe'), {
        onSuccess: () => {
            isAddToCollectionModalOpen.value = false;
            form.collection_id = '';
        },
    });
};

const addToMealPlan = () => {
    axios
        .post(route('meal-plans.add-recipe'), {
            meal_plan_id: mealPlanForm.meal_plan_id,
            recipe_id: mealPlanForm.recipe_id,
            scale_factor: mealPlanForm.scale_factor,
        })
        .then(() => {
            isAddToMealPlanModalOpen.value = false;
            mealPlanForm.meal_plan_id = '';
            // Optional success notification
        })
        .catch((error) => {
            console.error('Error adding recipe to meal plan:', error);
            // Handle validation errors if needed
        });
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
</script>

<template>
    <article class="group relative flex flex-col overflow-hidden rounded-lg border dark:border-gray-800">
        <Link :href="route('recipes.show', recipe.slug)" class="aspect-h-3 aspect-w-4 relative block overflow-hidden bg-gray-200 dark:bg-gray-800">
            <img
                v-if="recipe.images?.length"
                :src="recipe.images[0]"
                :alt="recipe.title"
                class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
            />
            <img
                v-else
                src="https://placehold.co/600x400?text=No+image+available"
                alt="No image available"
                class="h-full w-full object-cover object-center transition-transform duration-300 group-hover:scale-105"
            />
        </Link>

        <div class="flex flex-1 flex-col space-y-2 p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                <Link :href="route('recipes.show', recipe.slug)">
                    {{ recipe.title }}
                </Link>
            </h3>

            <div v-if="recipe.user?.slug" class="text-xs text-gray-600 dark:text-gray-400">
                <span>By </span>
                <Link
                    :href="route('recipes.by-user', { user: recipe.user.slug })"
                    class="hover:text-blue-600 hover:underline dark:hover:text-blue-400"
                >
                    {{ recipe.user.name }}
                </Link>
            </div>
            <div v-else class="text-xs text-gray-600 dark:text-gray-400">
                <span>By {{ recipe.user.name }}</span>
            </div>

            <p v-if="recipe.description" class="line-clamp-3 text-sm text-gray-500 dark:text-gray-400">
                {{ recipe.description }}
            </p>

            <div class="mt-auto space-y-6">
                <div class="flex flex-col space-y-2 text-xs">
                    <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                        <div class="flex items-center space-x-1">
                            <ClockIcon class="h-4 w-4" />
                            <span>{{ formatTime(recipe.prep_time + recipe.cooking_time) }}</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <UsersIcon class="h-4 w-4" />
                            <span>{{ recipe.servings }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-1 text-xs">
                    <a
                        v-if="recipe.url && sitename"
                        :href="recipe.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 font-medium text-blue-800 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-200 dark:hover:bg-blue-700"
                    >
                        {{ sitename }}
                    </a>
                    <Link
                        v-for="category in topCategories"
                        :key="category.id"
                        :href="route('categories.show', category.slug)"
                        class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        {{ category.name }}
                    </Link>
                </div>
            </div>
        </div>

        <!-- Add to Collection Button (Bottom Anchored) -->
        <div class="absolute bottom-0 right-0 p-2">
            <DropdownMenu>
                <DropdownMenuTrigger as="div">
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7 bg-white/80 shadow-sm backdrop-blur-sm hover:bg-white dark:bg-gray-800/80 dark:hover:bg-gray-700"
                    >
                        <EllipsisVerticalIcon class="h-4 w-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem @click="toggleFavorite">
                        <HeartIcon class="mr-2 h-4 w-4" :class="{ 'fill-current': isFavorited }" />
                        {{ isFavorited ? 'Unfavorite' : 'Favorite' }}
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="openAddToCollectionModal">
                        <FolderPlusIcon class="mr-2 h-4 w-4" />
                        Add to Collection
                    </DropdownMenuItem>
                    <DropdownMenuItem @click="openAddToMealPlanModal">
                        <UtensilsIcon class="mr-2 h-4 w-4" />
                        Add to Meal Plan
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </article>

    <!-- Add to Collection Modal -->
    <Dialog :open="isAddToCollectionModalOpen" @update:open="isAddToCollectionModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add to Collection</DialogTitle>
                <DialogDescription> Add this recipe to one of your collections. </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="addToCollection">
                <div class="space-y-4 py-4">
                    <div v-if="collections.length === 0" class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">You don't have any collections yet. Create a collection first.</p>
                        <div class="mt-4">
                            <Link
                                :href="route('collections.index')"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Go to Collections
                            </Link>
                        </div>
                    </div>
                    <div v-else class="space-y-2">
                        <Label for="collection">Select a Collection</Label>
                        <select
                            id="collection"
                            v-model="form.collection_id"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="" disabled>Select a collection</option>
                            <option v-for="collection in collections" :key="collection.id" :value="collection.id">
                                {{ collection.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.collection_id" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="isAddToCollectionModalOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="form.processing || collections.length === 0">Add</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Add to Meal Plan Modal -->
    <Dialog :open="isAddToMealPlanModalOpen" @update:open="isAddToMealPlanModalOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add to Meal Plan</DialogTitle>
                <DialogDescription>Add this recipe to one of your meal plans.</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="addToMealPlan">
                <div class="space-y-4 py-4">
                    <div v-if="mealPlans.length === 0" class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">You don't have any active meal plans. Create a meal plan first.</p>
                        <div class="mt-4">
                            <Link
                                :href="route('meal-plans.index')"
                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                Go to Meal Plans
                            </Link>
                        </div>
                    </div>
                    <div v-else class="space-y-2">
                        <Label for="mealplan">Select a Meal Plan</Label>
                        <select
                            id="mealplan"
                            v-model="mealPlanForm.meal_plan_id"
                            class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            required
                        >
                            <option value="" disabled>Select a meal plan</option>
                            <option v-for="plan in mealPlans" :key="plan.id" :value="plan.id">
                                {{ plan.name || new Date(plan.start_date).toLocaleDateString() }}
                            </option>
                        </select>
                        <InputError :message="mealPlanForm.errors.meal_plan_id" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="isAddToMealPlanModalOpen = false">Cancel</Button>
                    <Button type="submit" :disabled="mealPlanForm.processing || mealPlans.length === 0">Add</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
