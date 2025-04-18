<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import type { MealPlanDay } from '@/types/meal-plan';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

interface Props {
    open: boolean;
    mealPlanDay: MealPlanDay | null;
    availableRecipes: Array<{
        value: string;
        label: string;
    }>;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'meal-added': [];
}>();

const form = useForm({
    meal_plan_day_id: '',
    meal_plan_recipe_id: '',
    servings: 1,
    to_cook: false,
});

// Reset form when modal opens/closes or day changes
watch(
    () => [props.open, props.mealPlanDay],
    ([open, day]) => {
        if (open && day) {
            form.meal_plan_day_id = day.id.toString();
            form.meal_plan_recipe_id = props.availableRecipes.length > 0 ? props.availableRecipes[0].value : '';
            form.servings = 1;
            form.to_cook = false;
        }
    },
    { immediate: true },
);

const closeModal = () => {
    emit('update:open', false);
};

const addMealAssignment = async () => {
    await form.post(route('meal-assignments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            emit('meal-added');
            form.reset();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Add Meal to Day</DialogTitle>
                <DialogDescription>Select a recipe and specify the number of servings.</DialogDescription>
            </DialogHeader>
            <InputError v-if="form.errors.error" :message="form.errors.error" class="mt-2" />

            <form @submit.prevent="addMealAssignment" class="space-y-4">
                <div>
                    <Label for="recipe">Recipe</Label>
                    <Select id="recipe" v-model="form.meal_plan_recipe_id" :options="availableRecipes" class="mt-1 block w-full" />
                    <InputError :message="form.errors.meal_plan_recipe_id" class="mt-2" />
                </div>
                <div>
                    <Label for="servings">Servings</Label>
                    <Input id="servings" v-model="form.servings" type="number" step="1" min="1" max="20" class="mt-1 block w-full" />
                    <InputError :message="form.errors.servings" class="mt-2" />
                </div>
                <div class="flex items-center space-x-2">
                    <Checkbox id="to_cook" v-model="form.to_cook" />
                    <Label for="to_cook" class="font-normal">Mark as "to cook"</Label>
                </div>
                <DialogFooter>
                    <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Add Meal</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
