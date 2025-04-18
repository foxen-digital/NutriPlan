<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { formatStartDate as formatShortDate } from '@/utils/date';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

interface Props {
    open: boolean;
    mealPlanId: number;
    mealPlanName: string | null;
    mealPlanStartDate: string;
    mealPlanDuration: number;
    hasMealsToCook: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    period: 'full',
});

// Reset form when modal opens
watch(
    () => props.open,
    (open) => {
        if (open) {
            form.reset();
            form.period = 'full'; // Default to full period
            form.clearErrors();
        }
    },
);

const closeModal = () => {
    emit('update:open', false);
};

const generateShoppingList = () => {
    form.post(route('meal-plans.generate-shopping-list', props.mealPlanId), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const formatPeriod = (period: 'full' | 'week1' | 'week2'): string => {
    const startDate = new Date(props.mealPlanStartDate);
    startDate.setMinutes(startDate.getMinutes() + startDate.getTimezoneOffset()); // Adjust for timezone
    let endDate: Date;

    if (period === 'full') {
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + props.mealPlanDuration - 1);
    } else if (period === 'week1') {
        endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 6);
    } else {
        // week2
        const week2Start = new Date(startDate);
        week2Start.setDate(week2Start.getDate() + 7);
        endDate = new Date(week2Start);
        endDate.setDate(endDate.getDate() + 6);
        return `${formatShortDate(week2Start.toISOString())} - ${formatShortDate(endDate.toISOString())}`;
    }

    return `${formatShortDate(startDate.toISOString())} - ${formatShortDate(endDate.toISOString())}`;
};

const selectedPeriodLabel = computed(() => {
    const period = form.period as 'full' | 'week1' | 'week2';
    if (period === 'week1') return 'Week 1';
    if (period === 'week2') return 'Week 2';
    return 'Full Plan';
});

const defaultListName = computed(() => `Shopping List for ${props.mealPlanName || 'Meal Plan'} - ${selectedPeriodLabel.value}`);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Generate Shopping List</DialogTitle>
                <DialogDescription>Create a shopping list from this meal plan.</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="generateShoppingList">
                <div class="space-y-4 py-4">
                    <!-- Warning message -->
                    <div v-if="!hasMealsToCook" class="rounded-md bg-amber-50 p-4 dark:bg-amber-900/20">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                    No meals marked "to cook". Mark at least one meal as "to cook" before generating a shopping list.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <Label for="list-name">Shopping List Name (Optional)</Label>
                        <Input id="list-name" v-model="form.name" :placeholder="defaultListName" />
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use "{{ defaultListName }}"</p>
                    </div>
                    <div>
                        <Label for="period">Period</Label>
                        <select
                            id="period"
                            v-model="form.period"
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary dark:border-gray-800 dark:bg-gray-950 dark:text-white dark:ring-gray-800 sm:text-sm sm:leading-6"
                        >
                            <option value="full">Full Plan ({{ formatPeriod('full') }})</option>
                            <option v-if="mealPlanDuration === 14" value="week1">Week 1 ({{ formatPeriod('week1') }})</option>
                            <option v-if="mealPlanDuration === 14" value="week2">Week 2 ({{ formatPeriod('week2') }})</option>
                        </select>
                        <InputError :message="form.errors.period" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                    <Button type="submit" :disabled="!hasMealsToCook || form.processing">Generate List</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
