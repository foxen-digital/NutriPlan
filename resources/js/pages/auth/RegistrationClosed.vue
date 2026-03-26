<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthBase title="Registration closed" description="New account registration is not currently available.">
        <Head title="Registration Closed" />

        <div class="space-y-6 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This application is currently in private access. Please contact an administrator if you need an account.
            </p>

            <TextLink
                :href="route('login')"
                class="block font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300"
            >
                Back to login
            </TextLink>
        </div>
    </AuthBase>
</template>
