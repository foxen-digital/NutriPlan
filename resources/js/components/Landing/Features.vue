<script setup lang="ts">
import { BookOpen, CalendarDays, Copy, Heart, Import, ShoppingCart } from 'lucide-vue-next';
import { Motion, useInView } from 'motion-v';
import { ref } from 'vue';

const featureSection = ref(null);
// Use a bottom margin to detect when features section enters from the bottom
const inView = useInView(featureSection, {
    amount: 'some',
    margin: '400px 0px 0px 0px', // Large top margin to trigger as section approaches top
    once: true, // Only trigger once
});

const features = [
    {
        name: 'Recipe Management',
        description:
            'Effortlessly create new recipes, import from websites, or edit existing ones. Organize with categories & collections, scale servings, and control privacy settings.',
        icon: BookOpen,
    },
    {
        name: 'Effortless Meal Planning',
        description:
            'Build detailed weekly or monthly meal plans. Assign recipes to specific days, manage servings per meal, and track which meals are ready or need cooking.',
        icon: CalendarDays,
    },
    {
        name: 'Smart Shopping Lists',
        description:
            'Automatically generate lists from your meal plans. Create custom lists, track purchased items, and quickly add items using barcode scanning on mobile.',
        icon: ShoppingCart,
    },
    {
        name: 'Flexible Import & Scaling',
        description:
            'Seamlessly import recipes from your favorite cooking sites. Automatically scale ingredients up or down based on your desired number of servings.',
        icon: Import,
    },
    {
        name: 'Favorites & Discovery',
        description:
            'Quickly access your most-loved recipes by marking them as favorites. Explore a growing collection of recipes shared by the NutriPlan community.',
        icon: Heart,
    },
    {
        name: 'Plan Duplication',
        description:
            'Save time by easily copying existing meal plans. Perfect for reusing successful weekly schedules or making slight modifications for a new period.',
        icon: Copy,
    },
];

// Calculate delay based on index for staggered animation
const getDelay = (index: number): number => {
    return 0.15 * index;
};
</script>

<template>
    <section id="features" ref="featureSection" class="bg-gray-50 py-24 dark:bg-gray-900 sm:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">Your Culinary Command Center</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Everything you need to cook smarter</p>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    From finding and organizing recipes to planning meals and creating shopping lists, NutriPlan simplifies your kitchen workflow.
                </p>
            </div>
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
                    <Motion
                        v-for="(feature, index) in features"
                        :key="feature.name"
                        :initial="{ opacity: 0, rotateY: 90, scale: 0.8 }"
                        :animate="inView ? { opacity: 1, rotateY: 0, scale: 1 } : { opacity: 0, rotateY: 90, scale: 0.8 }"
                        :transition="{
                            type: 'spring',
                            stiffness: 50,
                            damping: 15,
                            delay: getDelay(index),
                            duration: 0.7,
                        }"
                        class="perspective-element flex flex-col"
                    >
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                            <component :is="feature.icon" class="h-5 w-5 flex-none text-indigo-600 dark:text-indigo-400" aria-hidden="true" />
                            {{ feature.name }}
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-300">
                            <p class="flex-auto">{{ feature.description }}</p>
                        </dd>
                    </Motion>
                </dl>
            </div>
        </div>
    </section>
</template>

<style scoped>
.perspective-element {
    transform-style: preserve-3d;
    perspective: 1000px;
}
</style>
