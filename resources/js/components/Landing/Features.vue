<script setup lang="ts">
import { BookOpen, CalendarDays, Copy, Heart, Import, ShoppingCart } from 'lucide-vue-next';

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
</script>

<template>
    <section id="features" class="bg-gray-50 py-24 dark:bg-gray-900 sm:py-32">
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
                    <div v-for="feature in features" :key="feature.name" class="flex flex-col">
                        <dt class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                            <component :is="feature.icon" class="h-5 w-5 flex-none text-indigo-600 dark:text-indigo-400" aria-hidden="true" />
                            {{ feature.name }}
                        </dt>
                        <dd class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-300">
                            <p class="flex-auto">{{ feature.description }}</p>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>
</template>
