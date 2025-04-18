<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { InputError } from '@/components/ui/input-error';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import { MinusIcon, PlusIcon } from 'lucide-vue-next';
import { watch } from 'vue';

interface Props {
    open: boolean;
    mealPlanId: number;
    mealPlanName: string | null;
    initialPeopleCount: number;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const form = useForm({
    name: '',
    start_date: '',
    people_count: 1,
});

// Reset form when modal opens or props change
watch(
    () => [props.open, props.initialPeopleCount],
    ([open, peopleCount]) => {
        if (open) {
            form.name = '';
            form.start_date = new Date().toISOString().slice(0, 10); // Today's date
            form.people_count = peopleCount;
            form.clearErrors();
        }
    },
    { immediate: true },
);

const closeModal = () => {
    emit('update:open', false);
};

const incrementPeople = () => {
    if (form.people_count < 20) {
        form.people_count++;
    }
};

const decrementPeople = () => {
    if (form.people_count > 1) {
        form.people_count--;
    }
};

const copyMealPlan = () => {
    form.post(route('meal-plans.copy', props.mealPlanId), {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    });
};

const defaultPlanName = `Copy of ${props.mealPlanName || 'Meal Plan'}`;
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Copy Meal Plan</DialogTitle>
                <DialogDescription>Create a new meal plan by copying this one.</DialogDescription>
            </DialogHeader>
            <form @submit.prevent="copyMealPlan">
                <div class="space-y-4 py-4">
                    <div>
                        <Label for="copy-name">New Plan Name (Optional)</Label>
                        <Input id="copy-name" v-model="form.name" :placeholder="defaultPlanName" />
                        <p class="mt-1 text-xs text-gray-500">Leave blank to use "{{ defaultPlanName }}"</p>
                    </div>
                    <div>
                        <Label for="copy-start_date">Start Date</Label>
                        <Input id="copy-start_date" type="date" v-model="form.start_date" required />
                        <InputError :message="form.errors.start_date" />
                    </div>
                    <div>
                        <Label for="copy-people_count">Number of People</Label>
                        <div class="flex items-center space-x-2">
                            <Button type="button" variant="outline" size="icon" @click="decrementPeople" :disabled="form.people_count <= 1">
                                <MinusIcon class="h-4 w-4" />
                            </Button>
                            <Input
                                id="copy-people_count"
                                type="number"
                                v-model="form.people_count"
                                class="w-20 text-center"
                                min="1"
                                max="20"
                                required
                            />
                            <Button type="button" variant="outline" size="icon" @click="incrementPeople" :disabled="form.people_count >= 20">
                                <PlusIcon class="h-4 w-4" />
                            </Button>
                        </div>
                        <InputError :message="form.errors.people_count" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
                    <Button type="submit" :disabled="form.processing">Copy Plan</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
