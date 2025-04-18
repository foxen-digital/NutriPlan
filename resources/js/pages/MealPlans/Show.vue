<template>
    <AppLayout>
        <Head :title="`${mealPlan.name || 'Meal Plan'} | NutriPlan`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-2xl font-semibold leading-6 text-gray-900 dark:text-white">
                        {{ mealPlan.name || `Meal Plan (${formatStartDate(mealPlan.start_date)})` }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                        {{ formatStartDate(mealPlan.start_date) }} to {{ formatEndDate(mealPlan.start_date, mealPlan.duration) }} •
                        {{ mealPlan.people_count }} people
                    </p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <div class="hidden items-center gap-2 sm:flex">
                        <Button variant="destructive" @click="confirmDeleteMealPlan">
                            <TrashIcon class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                        <Button variant="outline" @click="isCopyModalOpen = true">
                            <CopyIcon class="mr-2 h-4 w-4" />
                            Copy Plan
                        </Button>
                        <Button variant="outline" @click="showGenerateShoppingListModal">
                            <ShoppingCartIcon class="mr-2 h-4 w-4" />
                            Generate Shopping List
                        </Button>
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
                        <DropdownMenuItem @click="showGenerateShoppingListModal">
                            <ShoppingCartIcon class="mr-2 h-4 w-4" />
                            Generate Shopping List
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="isCopyModalOpen = true">
                            <CopyIcon class="mr-2 h-4 w-4" />
                            Copy Plan
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="confirmDeleteMealPlan" class="text-destructive">
                            <TrashIcon class="mr-2 h-4 w-4" />
                            Delete
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            <div class="mt-8 rounded-lg border dark:border-gray-800">
                <div class="p-6">
                    <div class="mb-6 flex justify-between">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recipes</h2>
                        <Button @click="() => (showAddRecipeModal = true)">
                            <PlusIcon class="mr-2 h-4 w-4" />
                            Add Recipe
                        </Button>
                    </div>

                    <div v-if="mealPlan.recipes && mealPlan.recipes.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <RecipeCard
                            v-for="recipe in mealPlan.recipes"
                            :key="recipe.id"
                            :recipe="recipe"
                            @edit="editRecipeInPlan"
                            @remove="confirmRemoveRecipe"
                        />
                    </div>
                    <div v-else class="rounded-md bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-center text-gray-700 dark:text-gray-300">
                            No recipes added to this meal plan yet. Click "Add Recipe" to get started.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Days Grid -->
            <div class="mx-auto mt-8 w-full">
                <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Plan Days</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7">
                            <div
                                v-for="day in daysWithDates"
                                :key="day.id"
                                class="flex min-h-[150px] flex-col justify-between rounded-lg border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div>
                                    <!-- Container for top part -->
                                    <h3 class="flex items-center justify-between font-semibold text-gray-900 dark:text-white">
                                        <span>Day {{ day.day_number }}</span>
                                        <Badge
                                            v-if="getToCookCount(day)"
                                            variant="secondary"
                                            class="ml-2 bg-amber-100 text-amber-800 hover:bg-amber-100 dark:bg-amber-900/50 dark:text-amber-400 dark:hover:bg-amber-900/50"
                                        >
                                            {{ getToCookCount(day) }} to cook
                                        </Badge>
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ day.date }}</p>

                                    <!-- Meal Assignments -->
                                    <div class="mt-6 flex-grow space-y-2">
                                        <div v-if="day.meal_assignments?.length" class="space-y-4">
                                            <MealAssignmentCard
                                                v-for="assignment in day.meal_assignments"
                                                :key="assignment.id"
                                                :assignment="assignment"
                                                @edit="editMealAssignment"
                                                @remove="removeMealAssignment"
                                                @toggled="handleToCookToggled"
                                            />
                                        </div>
                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">No meals assigned</div>
                                    </div>
                                </div>
                                <!-- Add Meal Button (always rendered if day exists) -->
                                <div class="mt-2">
                                    <!-- Container for the button -->
                                    <Button variant="outline" size="sm" class="w-full" @click="showAddMealAssignmentModal(day)">
                                        <PlusIcon class="mr-2 h-4 w-4" />
                                        Add Meal
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmationModal :open="showDeleteDialog" @update:open="showDeleteDialog = $event" @confirm="deleteMealPlan" />

        <!-- Remove Recipe Confirmation Modal -->
        <RemoveRecipeModal
            :open="showRemoveRecipeDialog"
            :recipe="recipeToRemove"
            :meal-plan-id="mealPlan.id"
            @update:open="showRemoveRecipeDialog = $event"
        />

        <!-- Copy Meal Plan Modal -->
        <CopyPlanModal
            :open="isCopyModalOpen"
            :meal-plan-id="mealPlan.id"
            :meal-plan-name="mealPlan.name"
            :initial-people-count="mealPlan.people_count"
            @update:open="isCopyModalOpen = $event"
        />

        <!-- Generate Shopping List Modal -->
        <GenerateShoppingListModal
            :open="isGenerateShoppingListModalOpen"
            :meal-plan-id="mealPlan.id"
            :meal-plan-name="mealPlan.name"
            :meal-plan-start-date="mealPlan.start_date"
            :meal-plan-duration="mealPlan.duration"
            :has-meals-to-cook="hasMealsToCook"
            @update:open="isGenerateShoppingListModalOpen = $event"
        />

        <!-- Add Recipe Modal -->
        <AddRecipeModal v-model:open="showAddRecipeModal" :meal-plan-id="mealPlan.id" @recipe-added="handleRecipeAdded" />

        <!-- Edit Recipe Scale Factor Modal -->
        <EditRecipeScaleFactorModal
            v-model:open="showEditRecipeModal"
            :recipe="recipeToEdit"
            :meal-plan-id="mealPlan.id"
            @save="handleUpdateRecipeScaleFactor"
        />

        <!-- START: Add Meal Assignment Modal -->
        <AddMealAssignmentModal
            :open="showAssignMealModal"
            :meal-plan-day="selectedDay"
            :available-recipes="availableRecipes"
            @update:open="showAssignMealModal = $event"
            @meal-added="router.reload({ only: ['mealPlan'] })"
        />
        <!-- END: Add Meal Assignment Modal -->

        <!-- START: Edit Meal Assignment Modal -->
        <EditMealAssignmentModal
            :open="showEditAssignmentModal"
            :assignment="selectedAssignment"
            @update:open="showEditAssignmentModal = $event"
            @assignment-updated="router.reload({ only: ['mealPlan'] })"
        />
        <!-- END: Edit Meal Assignment Modal -->
    </AppLayout>
</template>

<script setup lang="ts">
import MealAssignmentCard from '@/components/MealPlan/MealAssignmentCard.vue';
import AddMealAssignmentModal from '@/components/MealPlan/Modals/AddMealAssignmentModal.vue';
import AddRecipeModal from '@/components/MealPlan/Modals/AddRecipeModal.vue';
import CopyPlanModal from '@/components/MealPlan/Modals/CopyPlanModal.vue';
import DeleteConfirmationModal from '@/components/MealPlan/Modals/DeleteConfirmationModal.vue';
import EditMealAssignmentModal from '@/components/MealPlan/Modals/EditMealAssignmentModal.vue';
import EditRecipeScaleFactorModal from '@/components/MealPlan/Modals/EditRecipeScaleFactorModal.vue';
import GenerateShoppingListModal from '@/components/MealPlan/Modals/GenerateShoppingListModal.vue';
import RemoveRecipeModal from '@/components/MealPlan/Modals/RemoveRecipeModal.vue';
import RecipeCard from '@/components/MealPlan/RecipeCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import type { MealAssignment, MealPlan, MealPlanDay } from '@/types/meal-plan';
import type { Recipe } from '@/types/recipe';
import { formatEndDate, formatStartDate } from '@/utils/date';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { CopyIcon, MenuIcon, PlusIcon, ShoppingCartIcon, TrashIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface RecipeWithPivot extends Recipe {
    pivot: {
        id: number;
        scale_factor: number;
        servings_available: number;
    };
}

interface Props {
    mealPlan: MealPlan & {
        recipes?: RecipeWithPivot[];
        days?: MealPlanDay[];
    };
    availableMealPlans: Array<{
        id: number;
        name: string | null;
        start_date: string;
    }>;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);
const showAddRecipeModal = ref(false);
const showRemoveRecipeDialog = ref(false);
const showEditRecipeModal = ref(false);
const recipeToRemove = ref<RecipeWithPivot | null>(null);
const recipeToEdit = ref<RecipeWithPivot | null>(null);

const showEditAssignmentModal = ref(false);
const showAssignMealModal = ref(false);
const selectedDay = ref<MealPlanDay | null>(null);
const selectedAssignment = ref<MealAssignment | null>(null);
const isCopyModalOpen = ref(false);
const isGenerateShoppingListModalOpen = ref(false);

const daysWithDates = computed(() => {
    if (!props.mealPlan.days || !props.mealPlan.start_date) {
        return [];
    }
    const startDate = new Date(props.mealPlan.start_date);
    // Adjust for timezone offset to avoid date shifting
    startDate.setMinutes(startDate.getMinutes() + startDate.getTimezoneOffset());

    return props.mealPlan.days.map((day) => {
        const dayDate = new Date(startDate);
        dayDate.setDate(startDate.getDate() + day.day_number - 1);
        return {
            ...day,
            date: dayDate.toLocaleDateString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
            }),
        };
    });
});

const deleteMealPlan = () => {
    router.delete(route('meal-plans.destroy', props.mealPlan.id), {
        onSuccess: () => {
            showDeleteDialog.value = false;
        },
    });
};

const confirmDeleteMealPlan = () => {
    showDeleteDialog.value = true;
};

const confirmRemoveRecipe = (recipe: RecipeWithPivot) => {
    console.log('Confirming removal of recipe:', recipe.title);
    recipeToRemove.value = recipe;
    showRemoveRecipeDialog.value = true;
};

const editRecipeInPlan = (recipe: RecipeWithPivot) => {
    console.log('Editing recipe:', recipe.title);
    recipeToEdit.value = recipe;
    showEditRecipeModal.value = true;
};

const handleUpdateRecipeScaleFactor = (recipe: RecipeWithPivot, newScaleFactor: number) => {
    console.log('Updating scale factor for:', recipe.title, 'to:', newScaleFactor);
    showEditRecipeModal.value = false;

    // First remove the recipe
    router.delete(route('meal-plans.remove-recipe', [props.mealPlan.id, recipe.slug]), {
        preserveScroll: true,
        onSuccess: () => {
            // Then add it back with the new scale factor
            axios
                .post(route('meal-plans.add-recipe'), {
                    meal_plan_id: props.mealPlan.id,
                    recipe_id: recipe.id,
                    scale_factor: newScaleFactor,
                })
                .then(() => {
                    recipeToEdit.value = null;
                    // Refresh the meal plan data
                    router.reload({ only: ['mealPlan'] });
                })
                .catch((error) => {
                    console.error('Error updating recipe scale factor:', error);
                });
        },
    });
};

const availableRecipes = computed(() => {
    const recipes =
        props.mealPlan.recipes?.map((recipe: RecipeWithPivot) => {
            const recipeTitle = recipe.title;
            return {
                value: recipe.pivot.id.toString(),
                label: `${recipeTitle} (${recipe.pivot.servings_available} servings available)`,
            };
        }) ?? [];
    return recipes;
});

function showAddMealAssignmentModal(day: MealPlanDay) {
    selectedDay.value = day;
    showAssignMealModal.value = true;
}

function editMealAssignment(assignment: MealAssignment) {
    selectedAssignment.value = assignment;
    showEditAssignmentModal.value = true;
}

async function removeMealAssignment(assignment: MealAssignment) {
    if (!confirm('Are you sure you want to remove this meal assignment?')) return;

    await router.delete(route('meal-assignments.destroy', assignment.id), {
        preserveScroll: true,
    });
}

function handleToCookToggled(updatedAssignment: MealAssignment): void {
    // Find the day that contains this assignment
    const day = props.mealPlan.days?.find((d) => d.meal_assignments.some((a) => a.id === updatedAssignment.id));

    if (day) {
        // Find and update the assignment in the day
        const index = day.meal_assignments.findIndex((a) => a.id === updatedAssignment.id);
        if (index !== -1) {
            day.meal_assignments[index].to_cook = updatedAssignment.to_cook;
        }
    }
}

function getToCookCount(day: MealPlanDay): number {
    if (!day.meal_assignments || day.meal_assignments.length === 0) {
        return 0;
    }
    return day.meal_assignments.filter((a) => a.to_cook).length;
}

const showGenerateShoppingListModal = () => {
    isGenerateShoppingListModalOpen.value = true;
};

// Add a computed property to check if there are any meals to cook
const hasMealsToCook = computed(() => {
    if (!props.mealPlan.days) return false;
    // Check if any day has at least one meal assignment marked as "to cook"
    return props.mealPlan.days.some((day) => day.meal_assignments && day.meal_assignments.some((assignment) => assignment.to_cook));
});

const handleRecipeAdded = () => {
    router.reload({ only: ['mealPlan'] });
};
</script>
