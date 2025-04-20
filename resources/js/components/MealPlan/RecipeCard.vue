<template>
    <div class="flex flex-col rounded-md border p-4 dark:border-gray-700">
        <div class="mb-2 flex items-center gap-4">
            <div v-if="recipe.images && recipe.images.length > 0" class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md">
                <img :src="recipe.images[0]" alt="" class="h-full w-full object-cover" />
            </div>
            <div class="flex-grow">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <Link :href="route('recipes.show', recipe.slug)" class="hover:underline">
                        {{ recipe.title }}
                    </Link>
                </h3>
            </div>
            <div>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" class="h-8 w-8 flex-shrink-0">
                            <EllipsisVerticalIcon class="h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem @click="$emit('edit', recipe)">
                            <PencilIcon class="mr-2 h-4 w-4" />
                            Edit Scale Factor
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="$emit('remove', recipe)" class="text-red-600 focus:text-red-600 dark:focus:text-red-400">
                            <TrashIcon class="mr-2 h-4 w-4" />
                            Remove from Plan
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Scale Factor: {{ formatScaleFactor(recipe.pivot.scale_factor) }}x ({{ calculatedServings }} servings)
            <span class="ml-2 text-green-600 dark:text-green-400">
                • {{ formatScaleFactor(recipe.pivot.servings_available) }} available meals (for {{ peopleCount }})
            </span>
        </p>
    </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { Recipe } from '@/types/recipe';
import { Link } from '@inertiajs/vue3';
import { EllipsisVerticalIcon, PencilIcon, TrashIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface RecipeWithPivot extends Recipe {
    pivot: {
        id: number;
        scale_factor: number;
        servings_available: number;
    };
}

const props = defineProps<{
    recipe: RecipeWithPivot;
    peopleCount: number;
}>();

defineEmits<{
    (e: 'edit', recipe: RecipeWithPivot): void;
    (e: 'remove', recipe: RecipeWithPivot): void;
}>();

const formatScaleFactor = (factor: number | string): string => {
    const num = Number(factor);
    return Number.isInteger(num) ? num.toString() : num.toFixed(1);
};

const calculatedServings = computed((): number => {
    return Math.round(props.recipe.servings * props.recipe.pivot.scale_factor);
});
</script>
