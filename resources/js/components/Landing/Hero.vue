<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
defineProps<{
    authenticated: boolean;
}>();

const draw = {
    hidden: { pathLength: 0, opacity: 0 },
    visible: (custom: unknown) => {
        const delay = (custom as number) * 0.1;
        return {
            pathLength: 1,
            opacity: 1,
            transition: {
                pathLength: { delay, type: 'linear', duration: 1, bounce: 0 },
                opacity: { delay, duration: 0.1 },
            },
        };
    },
};
</script>

<template>
    <section class="relative min-h-screen overflow-hidden bg-white dark:bg-gray-900">
        <!-- Background pattern -->
        <div class="absolute inset-0 z-0 opacity-50 dark:opacity-20">
            <div
                class="absolute inset-y-0 right-1/2 -z-10 mr-16 w-[200%] origin-bottom-left skew-x-[-30deg] bg-indigo-100 shadow-xl shadow-indigo-500/20 ring-1 ring-indigo-200 dark:bg-gray-800/80 dark:ring-gray-700 sm:mr-28 lg:mr-0 xl:mr-16 xl:origin-center"
            ></div>
        </div>

        <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto py-12 sm:py-48 lg:py-48">
                <div class="flex flex-col md:flex-row md:items-center md:gap-8">
                    <!-- Left side - Text content with flex-grow -->
                    <div id="intro" class="text-center md:flex-grow md:pr-8 md:text-left lg:pr-16">
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                            Recipe Management for the
                            <span class="relative whitespace-nowrap text-indigo-600 dark:text-indigo-400">
                                <motion.svg
                                    aria-hidden="true"
                                    viewBox="0 0 418 42"
                                    class="absolute left-0 top-2/3 h-[0.58em] w-full fill-none"
                                    preserveAspectRatio="none"
                                    initial="hidden"
                                    animate="visible"
                                >
                                    <motion.path
                                        d="M203.371.916c-26.013-2.078-76.686 1.963-124.73 9.946L67.3 12.749C35.421 18.062 18.2 21.766 6.004 25.934 1.244 27.561.828 27.778.874 28.61c.07 1.214.828 1.121 9.595-1.176 9.072-2.377 17.15-3.92 39.246-7.496C123.565 7.986 157.869 4.492 195.942 5.046c7.461.108 19.25 1.696 19.17 2.582-.107 1.183-7.874 4.31-25.75 10.366-21.992 7.45-35.43 12.534-36.701 13.884-2.173 2.308-.202 4.407 4.442 4.734 2.654.187 3.263.157 15.593-.78 35.401-2.686 57.944-3.488 88.365-3.143 46.327.526 75.721 2.23 130.788 7.584 19.787 1.924 20.814 1.98 24.557 1.332l.066-.011c1.201-.203"
                                        stroke="rgba(79, 70, 229, 0.4)"
                                        stroke-width="6"
                                        :variants="draw"
                                        :custom="1"
                                    ></motion.path>
                                </motion.svg>
                                <span class="relative">Modern Kitchen</span></span
                            >
                        </h1>

                        <motion.div :initial="{ opacity: 0, x: 150 }" :animate="{ opacity: 1, x: 0 }" :transition="{ duration: 0.6 }">
                            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                                Organize your recipes, plan your meals, and streamline your cooking with NutriPlan. The perfect solution for home
                                cooks and food enthusiasts.
                            </p>
                        </motion.div>

                        <!-- Image for mobile -->
                        <motion.div
                            class="mt-6 flex-shrink-0 md:hidden"
                            :initial="{ opacity: 0, scale: 0.9 }"
                            :animate="{ opacity: 1, scale: 1 }"
                            :transition="{ duration: 0.8, delay: 0.2 }"
                        >
                            <div class="relative mx-auto w-full max-w-sm lg:max-w-md">
                                <img src="/img/recipes-ipad.png" alt="NutriPlan App on iPad" class="h-auto w-full rounded-lg shadow-2xl" />
                                <div
                                    class="absolute -inset-0.5 -z-10 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 opacity-30 blur"
                                ></div>
                            </div>
                        </motion.div>

                        <motion.div :initial="{ opacity: 0, y: 50 }" :animate="{ opacity: 1, y: 0 }" :transition="{ duration: 0.6 }">
                            <div class="mt-10 flex items-center justify-center gap-x-6 md:justify-start">
                                <Link
                                    v-if="!authenticated"
                                    :href="route('register')"
                                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                                >
                                    Get started
                                </Link>
                                <Link
                                    v-else
                                    :href="route('dashboard')"
                                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                                >
                                    Go to Dashboard
                                </Link>
                                <a href="#features" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">
                                    Learn more <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </motion.div>
                    </div>

                    <!-- Right side - Image with fixed width -->
                    <motion.div
                        class="mt-12 hidden md:mt-0 md:block md:flex-shrink-0"
                        :initial="{ opacity: 0, scale: 0.9 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.8, delay: 0.2 }"
                    >
                        <div class="relative mx-auto w-full max-w-sm lg:max-w-md">
                            <img src="/img/recipes-ipad.png" alt="NutriPlan App on iPad" class="h-auto w-full rounded-lg shadow-2xl" />
                            <div class="absolute -inset-0.5 -z-10 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 opacity-30 blur"></div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </section>
</template>
