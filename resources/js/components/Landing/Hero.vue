<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    authenticated: boolean;
}>();
</script>

<template>
    <section class="relative overflow-hidden bg-white dark:bg-gray-900 min-h-screen">
        <!-- Background pattern -->
        <div class="absolute inset-0 z-0 opacity-50 dark:opacity-20">
            <div
                class="absolute inset-y-0 right-1/2 -z-10 mr-16 w-[200%] origin-bottom-left skew-x-[-30deg] bg-indigo-100 shadow-xl shadow-indigo-500/20 ring-1 ring-indigo-200 dark:bg-gray-800/80 dark:ring-gray-700 sm:mr-28 lg:mr-0 xl:mr-16 xl:origin-center">
            </div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                        Recipe Management for the Modern Kitchen
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                        Organize your recipes, plan your meals, and streamline your cooking with NutriPlan. The perfect
                        solution for home cooks and
                        food enthusiasts.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <Link v-if="!authenticated" :href="route('register')"
                            class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                        Get started
                        </Link>
                        <Link v-else :href="route('dashboard')"
                            class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400">
                        Go to Dashboard
                        </Link>
                        <a href="#features" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">
                            Learn more <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
