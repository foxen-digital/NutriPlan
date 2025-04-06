<template>
    <AppLayout>
        <Head :title="`${mealPlan.name || 'Meal Plan'} | NutriPlan`" />

        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ mealPlan.name || `Meal Plan (${formatStartDate(mealPlan.start_date)})` }}
                    </h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-400">
                        {{ formatStartDate(mealPlan.start_date) }} to {{ formatEndDate(mealPlan.start_date, mealPlan.duration) }} •
                        {{ mealPlan.people_count }} people
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <Button variant="destructive" size="sm" @click="confirmDeleteMealPlan">
                        <TrashIcon class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                    <Button variant="outline" size="sm" @click="() => showCopyModal()">
                        <CopyIcon class="mr-2 h-4 w-4" />
                        Copy Plan
                    </Button>
                </div>
            </div>

            <div class="rounded-lg border dark:border-gray-800">
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
        <Dialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Delete Meal Plan</DialogTitle>
                    <DialogDescription> Are you sure you want to delete this meal plan? This action cannot be undone. </DialogDescription>
                </DialogHeader>
                <div class="flex items-center justify-end space-x-2 pt-4">
                    <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteMealPlan">Delete</Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Remove Recipe Confirmation Modal -->
        <Dialog :open="showRemoveRecipeDialog" @update:open="showRemoveRecipeDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Remove Recipe</DialogTitle>
                    <DialogDescription> Are you sure you want to remove "{{ recipeToRemove?.title }}" from this meal plan? </DialogDescription>
                </DialogHeader>
                <div class="flex items-center justify-end space-x-2 pt-4">
                    <Button variant="outline" @click="showRemoveRecipeDialog = false">Cancel</Button>
                    <Button variant="destructive" @click="removeRecipe">Remove</Button>
                </div>
            </DialogContent>
        </Dialog>

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
                            <p class="mt-1 text-xs text-gray-500">Leave blank to use "Copy of {{ mealPlan.name || 'Meal Plan' }}"</p>
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

        <!-- Add Recipe Modal -->
        <Dialog :open="showAddRecipeModal" @update:open="showAddRecipeModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Add Recipe to Meal Plan</DialogTitle>
                    <DialogDescription> Search for a recipe to add to your meal plan. </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="recipe-search">Search Recipes</Label>
                        <div class="relative">
                            <Input id="recipe-search" v-model="searchQuery" placeholder="Type to search..." @input="debounceSearch" />
                            <div v-if="isSearching" class="absolute right-3 top-2.5">
                                <Spinner class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                    </div>

                    <div v-if="searchResults.length > 0" class="max-h-60 overflow-y-auto rounded-md border p-2 dark:border-gray-700">
                        <div
                            v-for="recipe in searchResults"
                            :key="recipe.id"
                            class="cursor-pointer rounded-md p-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="selectRecipe(recipe)"
                        >
                            <div class="flex items-center gap-3">
                                <div v-if="recipe.images && recipe.images.length > 0" class="h-10 w-10 overflow-hidden rounded-md">
                                    <img :src="recipe.images[0]" alt="" class="h-full w-full object-cover" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ recipe.title }}</p>
                                    <p class="text-xs text-gray-500">{{ recipe.servings }} servings</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="searchQuery && !isSearching && searchResults.length === 0" class="rounded-md bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-center text-sm text-gray-500">No recipes found matching your search.</p>
                    </div>

                    <div v-if="selectedRecipe" class="rounded-md border p-3 dark:border-gray-700">
                        <h3 class="font-medium">{{ selectedRecipe.title }}</h3>
                        <div class="mt-3 space-y-2">
                            <div>
                                <Label for="scale-factor">Scale Factor</Label>
                                <Input id="scale-factor" v-model.number="scaleFactor" type="number" min="0.5" max="10" step="0.5" />
                                <p class="mt-1 text-xs text-gray-500">
                                    This will make approximately {{ calculateServings(selectedRecipe.servings, scaleFactor) }} servings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showAddRecipeModal = false">Cancel</Button>
                    <Button :disabled="!selectedRecipe" @click="addRecipeToMealPlan"> Add Recipe </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Edit Recipe Scale Factor Modal -->
        <Dialog :open="showEditRecipeModal" @update:open="showEditRecipeModal = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Edit Scale Factor</DialogTitle>
                    <DialogDescription> Adjust the scale factor for "{{ recipeToEdit?.title }}" </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div>
                        <Label for="edit-scale-factor">Scale Factor</Label>
                        <Input id="edit-scale-factor" v-model.number="editScaleFactor" type="number" min="0.5" max="10" step="0.5" />
                        <p class="mt-1 text-xs text-gray-500">
                            This will make approximately {{ recipeToEdit ? calculateServings(recipeToEdit.servings, editScaleFactor) : 0 }} servings
                        </p>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showEditRecipeModal = false">Cancel</Button>
                    <Button @click="updateRecipeScaleFactor">Save Changes</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- START: Add Meal Assignment Modal -->
        <Dialog :open="showAssignMealModal" @update:open="showAssignMealModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Add Meal to Day</DialogTitle>
                    <DialogDescription>Select a recipe and specify the number of servings.</DialogDescription>
                </DialogHeader>
                <InputError v-if="assignmentForm.errors.error" :message="assignmentForm.errors.error" class="mt-2" />

                <form @submit.prevent="addMealAssignment" class="space-y-4">
                    <div>
                        <Label for="recipe">Recipe</Label>
                        <Select id="recipe" v-model="assignmentForm.meal_plan_recipe_id" :options="availableRecipes" class="mt-1 block w-full" />
                        <InputError :message="assignmentForm.errors.meal_plan_recipe_id" class="mt-2" />
                    </div>
                    <div>
                        <Label for="servings">Servings</Label>
                        <Input id="servings" v-model="assignmentForm.servings" type="number" step="1" min="1" max="20" class="mt-1 block w-full" />
                        <InputError :message="assignmentForm.errors.servings" class="mt-2" />
                    </div>
                    <div class="flex items-center space-x-2">
                        <Checkbox id="to_cook" v-model="assignmentForm.to_cook" />
                        <Label for="to_cook" class="font-normal">Mark as "to cook"</Label>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="showAssignMealModal = false">Cancel</Button>
                        <Button type="submit" :disabled="assignmentForm.processing">Add Meal</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <!-- END: Add Meal Assignment Modal -->

        <!-- START: Edit Meal Assignment Modal -->
        <Dialog :open="showEditAssignmentModal" @update:open="showEditAssignmentModal = $event">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Meal Assignment</DialogTitle>
                    <DialogDescription>Update the number of servings for this meal.</DialogDescription>
                </DialogHeader>
                <InputError v-if="editAssignmentForm.errors.error" :message="editAssignmentForm.errors.error" class="mt-2" />

                <form @submit.prevent="updateMealAssignment" class="space-y-4">
                    <div>
                        <Label for="edit-servings">Servings</Label>
                        <Input
                            id="edit-servings"
                            v-model="editAssignmentForm.servings"
                            type="number"
                            step="1"
                            min="1"
                            max="20"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="editAssignmentForm.errors.servings" class="mt-2" />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="showEditAssignmentModal = false">Cancel</Button>
                        <Button type="submit" :disabled="editAssignmentForm.processing">Update</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
        <!-- END: Edit Meal Assignment Modal -->
    </AppLayout>
</template>

<script setup lang="ts">
import MealAssignmentCard from '@/components/MealPlan/MealAssignmentCard.vue';
import RecipeCard from '@/components/MealPlan/RecipeCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import Spinner from '@/components/ui/spinner.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { MealAssignment, MealPlan, MealPlanDay } from '@/types/meal-plan';
import type { Recipe } from '@/types/recipe';
import { formatEndDate, formatStartDate } from '@/utils/date';
import { Head, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { CopyIcon, MinusIcon, PlusIcon, TrashIcon } from 'lucide-vue-next';
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

interface AssignmentFormData {
    meal_plan_day_id: string;
    meal_plan_recipe_id: string;
    servings: number;
    to_cook: boolean;
    error?: string;
    [key: string]: any;
}

interface EditAssignmentFormData {
    servings: number;
    error?: string;
    [key: string]: any;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);
const showAddRecipeModal = ref(false);
const showRemoveRecipeDialog = ref(false);
const showEditRecipeModal = ref(false);
const recipeToRemove = ref<RecipeWithPivot | null>(null);
const recipeToEdit = ref<RecipeWithPivot | null>(null);

const searchQuery = ref('');
const searchResults = ref<Recipe[]>([]);
const isSearching = ref(false);
const selectedRecipe = ref<Recipe | null>(null);
const scaleFactor = ref(1.0);
const editScaleFactor = ref(1.0);

const showEditAssignmentModal = ref(false);
const showAssignMealModal = ref(false);
const selectedDay = ref<MealPlanDay | null>(null);
const selectedAssignment = ref<MealAssignment | null>(null);
const isCopyModalOpen = ref(false);

const copyForm = useForm({
    name: '',
    start_date: new Date().toISOString().slice(0, 10), // Today's date in YYYY-MM-DD format
    people_count: props.mealPlan.people_count,
});

const assignmentForm = useForm<AssignmentFormData>({
    meal_plan_day_id: '',
    meal_plan_recipe_id: '',
    servings: 1,
    to_cook: false,
});

const editAssignmentForm = useForm<EditAssignmentFormData>({
    servings: 1,
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

let searchTimeout: ReturnType<typeof setTimeout>;

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    if (searchQuery.value) {
        isSearching.value = true;
        searchTimeout = setTimeout(() => {
            searchRecipes();
        }, 300);
    } else {
        searchResults.value = [];
        isSearching.value = false;
    }
};

const searchRecipes = async () => {
    try {
        const response = await axios.get(route('api.recipes.search'), {
            params: { query: searchQuery.value },
            withCredentials: true,
        });
        searchResults.value = response.data.data;
    } catch (error) {
        console.error('Error searching recipes:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const selectRecipe = (recipe: Recipe) => {
    selectedRecipe.value = recipe;
    scaleFactor.value = 1.0;
    searchQuery.value = '';
    searchResults.value = [];
};

const calculateServings = (originalServings: number, scaleFactor: number): number => {
    return Math.round(originalServings * scaleFactor);
};

const addRecipeToMealPlan = () => {
    if (!selectedRecipe.value) return;

    router.post(
        route('meal-plans.add-recipe'),
        {
            meal_plan_id: props.mealPlan.id,
            recipe_id: selectedRecipe.value.id,
            scale_factor: scaleFactor.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddRecipeModal.value = false;
                selectedRecipe.value = null;
                scaleFactor.value = 1.0;
            },
        },
    );
};

const confirmRemoveRecipe = (recipe: RecipeWithPivot) => {
    console.log('Confirming removal of recipe:', recipe.title);
    recipeToRemove.value = recipe;
    showRemoveRecipeDialog.value = true;
};

const removeRecipe = () => {
    if (!recipeToRemove.value) return;
    console.log('Removing recipe:', recipeToRemove.value.title);
    router.delete(
        route('meal-plans.remove-recipe', {
            id: props.mealPlan.id,
            recipeId: recipeToRemove.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                showRemoveRecipeDialog.value = false;
                recipeToRemove.value = null;
            },
        },
    );
};

const editRecipeInPlan = (recipe: RecipeWithPivot) => {
    console.log('Editing recipe:', recipe.title);
    recipeToEdit.value = recipe;
    editScaleFactor.value = recipe.pivot.scale_factor;
    showEditRecipeModal.value = true;
};

const updateRecipeScaleFactor = () => {
    console.log('Updating scale factor for:', recipeToEdit.value?.title, 'to:', editScaleFactor.value);
    showEditRecipeModal.value = false;
    if (!recipeToEdit.value) return;

    // First remove the recipe
    router.delete(
        route('meal-plans.remove-recipe', {
            id: props.mealPlan.id,
            recipeId: recipeToEdit.value.id,
        }),
        {
            preserveScroll: true,
            onSuccess: () => {
                // Then add it back with the new scale factor
                router.post(
                    route('meal-plans.add-recipe'),
                    {
                        meal_plan_id: props.mealPlan.id,
                        recipe_id: recipeToEdit.value!.id,
                        scale_factor: editScaleFactor.value,
                    },
                    {
                        preserveScroll: true,
                        onSuccess: () => {
                            showEditRecipeModal.value = false;
                            recipeToEdit.value = null;
                        },
                    },
                );
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
    assignmentForm.meal_plan_day_id = day.id.toString();
    showAssignMealModal.value = true;
}

async function addMealAssignment() {
    await assignmentForm.post(route('meal-assignments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showAssignMealModal.value = false;
            assignmentForm.reset();
        },
    });
}

function editMealAssignment(assignment: MealAssignment) {
    selectedAssignment.value = assignment;
    editAssignmentForm.servings = assignment.servings;
    showEditAssignmentModal.value = true;
}

async function updateMealAssignment() {
    if (!selectedAssignment.value) return;

    await editAssignmentForm.put(route('meal-assignments.update', selectedAssignment.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditAssignmentModal.value = false;
            editAssignmentForm.reset();
            selectedAssignment.value = null;
        },
    });
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
</script>
