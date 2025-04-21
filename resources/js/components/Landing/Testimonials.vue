<script setup lang="ts">
import { Motion, useInView } from 'motion-v';
import { ref } from 'vue';

// Set up inView ref for the testimonials section with better options
const testimonialSection = ref(null);
// Use a bottom margin to detect when element enters from the bottom of viewport
const inView = useInView(testimonialSection, {
    amount: 'some', // Trigger when any part of element is visible
    margin: '400px 0px 0px 0px', // Large top margin to trigger as section approaches top
    once: true, // Only trigger once
});
</script>

<template>
    <section id="testimonials" ref="testimonialSection" class="bg-gray-50 py-24 dark:bg-gray-900 sm:py-32">
        <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">What our users are saying</p>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    Join thousands of home cooks who have transformed their cooking experience with NutriPlan.
                </p>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Motion
                    :initial="{ opacity: 0, y: 50 }"
                    :animate="inView ? { opacity: 1, y: 0 } : { opacity: 0, y: 50 }"
                    :transition="{ duration: 0.6, delay: 0.4 }"
                >
                    <div class="rounded-lg border p-6 shadow-sm dark:bg-gray-800/80">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <span class="font-semibold text-primary">JD</span>
                            </div>
                            <div>
                                <h4 class="font-semibold">Jane Doe</h4>
                                <p class="text-sm text-muted-foreground">Home Cook</p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            "NutriPlan has completely changed how I approach cooking. The meal planning feature saves me hours each week!"
                        </p>
                    </div>
                </Motion>
                <Motion
                    :initial="{ opacity: 0, y: 50 }"
                    :animate="inView ? { opacity: 1, y: 0 } : { opacity: 0, y: 50 }"
                    :transition="{ duration: 0.6, delay: 0.6 }"
                >
                    <div class="rounded-lg border p-6 shadow-sm dark:bg-gray-800/80">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <span class="font-semibold text-primary">MS</span>
                            </div>
                            <div>
                                <h4 class="font-semibold">Mark Smith</h4>
                                <p class="text-sm text-muted-foreground">Food Enthusiast</p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            "I love how easy it is to import recipes from anywhere on the web. The shopping list feature is a game-changer."
                        </p>
                    </div>
                </Motion>
                <Motion
                    :initial="{ opacity: 0, y: 50 }"
                    :animate="inView ? { opacity: 1, y: 0 } : { opacity: 0, y: 50 }"
                    :transition="{ duration: 0.6, delay: 0.8 }"
                >
                    <div class="rounded-lg border p-6 shadow-sm dark:bg-gray-800/80">
                        <div class="mb-4 flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                <span class="font-semibold text-primary">AL</span>
                            </div>
                            <div>
                                <h4 class="font-semibold">Amy Lee</h4>
                                <p class="text-sm text-muted-foreground">Busy Parent</p>
                            </div>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            "As a parent of three, NutriPlan has made meal planning so much easier. I can quickly plan a week's worth of meals and
                            generate a shopping list."
                        </p>
                    </div>
                </Motion>
            </div>
        </div>
    </section>
</template>
