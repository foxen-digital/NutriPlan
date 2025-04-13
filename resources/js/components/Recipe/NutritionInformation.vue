<template>
    <div class="rounded-lg border">
        <div v-if="nutrition" class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
            <div v-if="nutrition.calories" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Calories</span>
                <span class="font-medium">{{ nutrition.calories }}</span>
            </div>

            <div v-if="nutrition.carbohydrate_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Carbohydrates</span>
                <span class="font-medium">{{ nutrition.carbohydrate_content }}</span>
            </div>

            <div v-if="nutrition.protein_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Protein</span>
                <span class="font-medium">{{ nutrition.protein_content }}</span>
            </div>

            <div v-if="nutrition.fat_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Fat</span>
                <span class="font-medium">{{ nutrition.fat_content }}</span>
            </div>

            <div v-if="nutrition.fiber_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Fiber</span>
                <span class="font-medium">{{ nutrition.fiber_content }}</span>
            </div>

            <div v-if="nutrition.sugar_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Sugar</span>
                <span class="font-medium">{{ nutrition.sugar_content }}</span>
            </div>

            <div v-if="nutrition.cholesterol_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Cholesterol</span>
                <span class="font-medium">{{ nutrition.cholesterol_content }}</span>
            </div>

            <div v-if="nutrition.sodium_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Sodium</span>
                <span class="font-medium">{{ nutrition.sodium_content }}</span>
            </div>

            <div v-if="nutrition.saturated_fat_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Saturated Fat</span>
                <span class="font-medium">{{ nutrition.saturated_fat_content }}</span>
            </div>

            <div v-if="nutrition.trans_fat_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Trans Fat</span>
                <span class="font-medium">{{ nutrition.trans_fat_content }}</span>
            </div>

            <div v-if="nutrition.unsaturated_fat_content" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Unsaturated Fat</span>
                <span class="font-medium">{{ nutrition.unsaturated_fat_content }}</span>
            </div>

            <div v-if="nutrition.serving_size" class="flex flex-col">
                <span class="text-sm text-muted-foreground">Serving Size</span>
                <span class="font-medium">{{ nutrition.serving_size }}</span>
            </div>
        </div>

        <div v-else class="py-4 text-center text-muted-foreground">No nutrition information available</div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    nutrition?: {
        calories?: string;
        carbohydrate_content?: string;
        cholesterol_content?: string;
        fat_content?: string;
        fiber_content?: string;
        protein_content?: string;
        saturated_fat_content?: string;
        serving_size?: string;
        sodium_content?: string;
        sugar_content?: string;
        trans_fat_content?: string;
        unsaturated_fat_content?: string;
    } | null;
}>();
</script>
