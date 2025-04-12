<script setup lang="ts">
import CallToAction from '@/components/Landing/CallToAction.vue';
import Features from '@/components/Landing/Features.vue';
import Hero from '@/components/Landing/Hero.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    auth: {
        user: any;
    };
}>();
</script>

<template>
    <Head title="Welcome to NutriPlan">
        <meta name="description" content="NutriPlan - Modern recipe management and meal planning for home cooks" />
    </Head>

    <main class="bg-white dark:bg-gray-900">
        <Hero :authenticated="!!auth.user" />
        <Features />
        <CallToAction :authenticated="!!auth.user" />
    </main>
</template>
