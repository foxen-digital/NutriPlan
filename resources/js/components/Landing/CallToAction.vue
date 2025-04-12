<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    authenticated: boolean;
}>();
</script>

<template>
    <section class="relative isolate overflow-hidden bg-gray-50 dark:bg-gray-900">
        <div class="px-6 py-24 sm:px-6 sm:py-32 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                    Ready to start cooking?
                    <br />
                    Join NutriPlan today.
                </h2>
                <p class="mx-auto mt-6 max-w-xl text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Start organizing your recipes, planning your meals, and discovering new dishes. Join our community
                    of home cooks today.
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
        <svg viewBox="0 0 1024 1024"
            class="absolute left-1/2 top-1/2 -z-10 h-[64rem] w-[64rem] -translate-x-1/2 [mask-image:radial-gradient(closest-side,white,transparent)]"
            aria-hidden="true">
            <circle cx="512" cy="512" r="512" fill="url(#gradient)" fill-opacity="0.7" />
            <defs>
                <radialGradient id="gradient">
                    <stop stop-color="#7775D6" />
                    <stop offset="1" stop-color="#E935C1" />
                </radialGradient>
            </defs>
        </svg>
    </section>
</template>
