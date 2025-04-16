<script setup lang="ts">
import CallToAction from '@/components/Landing/CallToAction.vue';
import Features from '@/components/Landing/Features.vue';
import Hero from '@/components/Landing/Hero.vue';
import Menu from '@/components/Landing/Menu.vue';
import Testimonials from '@/components/Landing/Testimonials.vue';
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
        <Menu :loggedIn="!!auth.user" />
        <Hero :authenticated="!!auth.user" />
        <Features />
        <Testimonials />
        <CallToAction :authenticated="!!auth.user" />
    </main>
</template>
