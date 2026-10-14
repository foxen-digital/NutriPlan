<template>
    <Dialog :open="open" @update:open="$emit('update:open', $event)">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Bulk Add Ingredients</DialogTitle>
                <DialogDescription> Paste one ingredient per line, e.g. "200 g plain flour" or "2 red onions, finely chopped". </DialogDescription>
            </DialogHeader>
            <form @submit.prevent="submit">
                <div class="grid gap-4 py-4">
                    <div class="grid gap-2">
                        <Label for="bulk-ingredients">Ingredients</Label>
                        <Textarea
                            id="bulk-ingredients"
                            v-model="rawIngredients"
                            rows="8"
                            class="p-3"
                            placeholder="200 g plain flour&#10;2 red onions, finely chopped&#10;1 tbsp olive oil"
                            required
                        />
                        <InputError :message="error" />
                    </div>
                </div>
                <DialogFooter>
                    <Button type="button" variant="ghost" @click="$emit('update:open', false)"> Cancel </Button>
                    <Button type="submit" :disabled="isSubmitting">
                        <Loader2Icon v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Add Ingredients
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { NormalizedIngredient } from '@/types/ingredient';
import axios from 'axios';
import { Loader2Icon } from 'lucide-vue-next';
import { ref } from 'vue';

const emit = defineEmits<{
    'update:open': [value: boolean];
    'ingredients-added': [ingredients: NormalizedIngredient[]];
}>();

defineProps<{
    open: boolean;
}>();

const rawIngredients = ref('');
const isSubmitting = ref(false);
const error = ref('');

const parseLines = (): string[] =>
    rawIngredients.value
        .split('\n')
        .map((line) => line.trim())
        .filter((line) => line !== '');

const extractErrorMessage = (e: unknown): string => {
    if (axios.isAxiosError(e)) {
        const errors = (e.response?.data as { errors?: Record<string, string[]> } | undefined)?.errors;

        if (errors?.ingredients?.[0]) {
            return errors.ingredients[0];
        }
    }

    return 'Could not process the ingredients. Please try again.';
};

const submit = async () => {
    const ingredients = parseLines();

    if (ingredients.length === 0) {
        return;
    }

    isSubmitting.value = true;
    error.value = '';

    try {
        const response = await axios.post(route('ingredients.normalize'), { ingredients });

        emit('ingredients-added', response.data.ingredients);
        rawIngredients.value = '';
        emit('update:open', false);
    } catch (e) {
        error.value = extractErrorMessage(e);
        console.error('Error normalizing ingredients:', e);
    } finally {
        isSubmitting.value = false;
    }
};
</script>
