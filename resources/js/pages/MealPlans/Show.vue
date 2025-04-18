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
                        <Button variant="outline" @click="showCopyModal">
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
                        <DropdownMenuItem @click="showCopyModal">
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
        <Dialog :open="isCopyModalOpen" @update:open="isCopyModalOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Copy Meal Plan</DialogTitle>
                    <DialogDescription>Create a new meal plan by copying this one.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="copyMealPlan">
                    <div class="space-y-4 py-4">
                        <div>
                            <Label for="name">New Plan Name (Optional)</Label>
                            <Input id="name" v-model="copyForm.name" placeholder="e.g., Copy of Weekly Plan" />
                            <p class="mt-1 text-xs text-gray-500">Leave blank to use "Copy of {{ props.mealPlan.name || 'Meal Plan' }}"</p>
                        </div>
                        <div>
                            <Label for="start_date">Start Date</Label>
                            <Input id="start_date" type="date" v-model="copyForm.start_date" required />
                            <InputError :message="copyForm.errors.start_date" />
                        </div>
                        <div>
                            <Label for="people_count">Number of People</Label>
                            <div class="flex items-center space-x-2">
                                <Button type="button" variant="outline" size="icon" @click="decrementPeople" :disabled="copyForm.people_count <= 1">
                                    <MinusIcon class="h-4 w-4" />
                                </Button>
                                <Input
                                    id="people_count"
                                    type="number"
                                    v-model="copyForm.people_count"
                                    class="w-20 text-center"
                                    min="1"
                                    max="20"
                                    required
                                />
                                <Button type="button" variant="outline" size="icon" @click="incrementPeople" :disabled="copyForm.people_count >= 20">
                                    <PlusIcon class="h-4 w-4" />
                                </Button>
                            </div>
                            <InputError :message="copyForm.errors.people_count" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isCopyModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="copyForm.processing">Copy Plan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Generate Shopping List Modal -->
        <Dialog :open="isGenerateShoppingListModalOpen" @update:open="isGenerateShoppingListModalOpen = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Generate Shopping List</DialogTitle>
                    <DialogDescription>Create a shopping list from this meal plan.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="generateShoppingList">
                    <div class="space-y-4 py-4">
                        <!-- Add warning message if no meals are flagged to cook -->
                        <div v-if="!hasMealsToCook" class="rounded-md bg-amber-50 p-4 dark:bg-amber-900/20">
                            <div class="flex">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                        No meals marked "to cook" in this meal plan. Please mark at least one meal as "to cook" before generating a
                                        shopping list.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <Label for="list-name">Shopping List Name (Optional)</Label>
                            <Input id="list-name" v-model="shoppingListForm.name" placeholder="e.g., Groceries for the week" />
                            <p class="mt-1 text-xs text-gray-500">
                                Leave blank to use "Shopping List for {{ props.mealPlan.name || 'Meal Plan' }} - {{ selectedPeriodLabel }}"
                            </p>
                        </div>
                        <div>
                            <Label for="period">Period</Label>
                            <select
                                id="period"
                                v-model="shoppingListForm.period"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:ring-gray-800 sm:text-sm sm:leading-6"
                            >
                                <option value="full">Full Plan ({{ formatPeriod('full') }})</option>
                                <option v-if="props.mealPlan.duration === 14" value="week1">Week 1 ({{ formatPeriod('week1') }})</option>
                                <option v-if="props.mealPlan.duration === 14" value="week2">Week 2 ({{ formatPeriod('week2') }})</option>
                            </select>
                            <InputError :message="shoppingListForm.errors.period" />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isGenerateShoppingListModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="!hasMealsToCook || shoppingListForm.processing">Generate List</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

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
import DeleteConfirmationModal from '@/components/MealPlan/Modals/DeleteConfirmationModal.vue';
import EditMealAssignmentModal from '@/components/MealPlan/Modals/EditMealAssignmentModal.vue';
import EditRecipeScaleFactorModal from '@/components/MealPlan/Modals/EditRecipeScaleFactorModal.vue';
import RemoveRecipeModal from '@/components/MealPlan/Modals/RemoveRecipeModal.vue';
import RecipeCard from '@/components/MealPlan/RecipeCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { MealAssignment, MealPlan, MealPlanDay } from '@/types/meal-plan';
import type { Recipe } from '@/types/recipe';
import { formatEndDate, formatStartDate } from '@/utils/date';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { CopyIcon, MenuIcon, MinusIcon, PlusIcon, ShoppingCartIcon, TrashIcon } from 'lucide-vue-next';
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

const copyForm = useForm({
    name: '',
    start_date: new Date().toISOString().slice(0, 10), // Today's date in YYYY-MM-DD format
    people_count: props.mealPlan.people_count,
});

const shoppingListForm = useForm({
    name: '',
    period: 'full',
});

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
    router.delete(
        route('meal-plans.remove-recipe', {
            id: props.mealPlan.id,
            recipeId: recipe.id,
        }),
        {
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
        },
    );
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

const showCopyModal = () => {
    copyForm.name = '';
    copyForm.start_date = new Date().toISOString().slice(0, 10);
    copyForm.people_count = props.mealPlan.people_count;
    isCopyModalOpen.value = true;
};

const incrementPeople = () => {
    if (copyForm.people_count < 20) {
        copyForm.people_count++;
    }
};

const decrementPeople = () => {
    if (copyForm.people_count > 1) {
        copyForm.people_count--;
    }
};

const copyMealPlan = () => {
    copyForm.post(route('meal-plans.copy', props.mealPlan.id), {
        onSuccess: () => {
            isCopyModalOpen.value = false;
        },
    });
};

const showGenerateShoppingListModal = () => {
    // Reset the form and show the modal
    shoppingListForm.reset();
    shoppingListForm.period = 'full';
    isGenerateShoppingListModalOpen.value = true;
};

const selectedPeriodLabel = computed(() => {
    const period = shoppingListForm.period;
    if (period === 'week1') return 'Week 1';
    if (period === 'week2') return 'Week 2';
    return 'Full Plan';
});

const formatPeriod = (period: 'full' | 'week1' | 'week2'): string => {
    const startDate = new Date(props.mealPlan.start_date);
    let endDate;

    if (period === 'full') {
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + props.mealPlan.duration - 1);
    } else if (period === 'week1') {
        endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 6);
    } else {
        // week2
        const week2Start = new Date(startDate);
        week2Start.setDate(week2Start.getDate() + 7);
        endDate = new Date(week2Start);
        endDate.setDate(endDate.getDate() + 6);
        return `${formatShortDate(week2Start)} - ${formatShortDate(endDate)}`;
    }

    return `${formatShortDate(startDate)} - ${formatShortDate(endDate)}`;
};

const formatShortDate = (date: Date): string => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const generateShoppingList = () => {
    shoppingListForm.post(route('meal-plans.generate-shopping-list', props.mealPlan.id), {
        onSuccess: () => {
            isGenerateShoppingListModalOpen.value = false;
            shoppingListForm.reset();
        },
    });
};

// Add a computed property to check if there are any meals to cook
const hasMealsToCook = computed(() => {
    if (!props.mealPlan.days) return false;

    // Check if any day has at least one meal assignment marked as "to cook"
    return props.mealPlan.days.some((day) => day.meal_assignments && day.meal_assignments.some((assignment) => assignment.to_cook));
});

const handleRecipeAdded = () => {
    // Handle the event when a new recipe is added
    // This function should be implemented to refresh the meal plan data
    router.reload({ only: ['mealPlan'] });
};
</script>
