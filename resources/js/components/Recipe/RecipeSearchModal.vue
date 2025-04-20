<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import type { RecipeFilters } from '@/types/recipes'; // Assuming this type exists or needs creation
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Props {
    open: boolean;
    currentFilters: RecipeFilters;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:open']);

const internalOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
});

const searchTerm = ref('');
const searchMode = ref('name_description'); // 'name_description' or 'ingredient'

// Watch for prop changes to initialize/reset local state when modal opens
watch(
    () => props.open,
    (newVal) => {
        if (newVal) {
            searchTerm.value = props.currentFilters.search_term || '';
            searchMode.value = props.currentFilters.search_mode || 'name_description';
        } else {
            // Optionally reset when closing if desired, though re-initializing on open is usually sufficient
            // searchTerm.value = ''
            // searchMode.value = 'name_description'
        }
    },
    { immediate: true },
);

const performSearch = () => {
    // Preserve existing filters (like category, show_mine) and add/update search filters
    const queryParams = { ...route().params }; // Get existing query params from Inertia/Ziggy

    if (searchTerm.value.trim()) {
        queryParams.search_term = searchTerm.value.trim();
        queryParams.search_mode = searchMode.value;
    } else {
        // If search term is empty, remove search params from the query
        delete queryParams.search_term;
        delete queryParams.search_mode;
    }

    // Reset pagination when performing a new search
    delete queryParams.page;

    router.get(route('recipes.index'), queryParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true, // Avoids polluting browser history with intermediate search states
        onSuccess: () => {
            internalOpen.value = false; // Close modal on successful search
        },
    });
};

const clearSearch = () => {
    const queryParams = { ...route().params };
    delete queryParams.search_term;
    delete queryParams.search_mode;
    delete queryParams.page; // Also reset pagination when clearing

    router.get(route('recipes.index'), queryParams, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            internalOpen.value = false;
        },
    });
};

const hasActiveSearch = computed(() => !!props.currentFilters.search_term);
</script>

<template>
    <Dialog v-model:open="internalOpen">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Search Recipes</DialogTitle>
                <DialogDescription> Find recipes by name, description, or ingredients. </DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <Label for="recipe-search" aria-hidden="false" hidden>Search Recipes</Label>
                    <div class="relative">
                        <Input id="recipe-search" v-model="searchTerm" @keyup.enter="performSearch" />
                    </div>
                </div>

                <div class="grid grid-cols-4 items-center gap-4">
                    <Label class="">Search by:</Label>
                    <RadioGroup v-model="searchMode" class="col-span-3 flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="r1" value="name_description" />
                            <Label for="r1">Name/Description</Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <RadioGroupItem id="r2" value="ingredient" />
                            <Label for="r2">Ingredient</Label>
                        </div>
                    </RadioGroup>
                </div>
            </div>
            <DialogFooter class="sm:justify-between">
                <Button type="button" variant="outline" @click="clearSearch" v-if="hasActiveSearch">Clear Search</Button>
                <div class="flex justify-end gap-2" v-else></div>
                <!-- Spacer to keep buttons aligned -->
                <div class="flex gap-2">
                    <DialogClose as-child>
                        <Button type="button" variant="secondary"> Cancel </Button>
                    </DialogClose>
                    <Button type="button" @click="performSearch">Search</Button>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
