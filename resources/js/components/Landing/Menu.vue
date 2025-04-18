<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { Utensils } from 'lucide-vue-next';

defineProps<{
    loggedIn: boolean;
}>();
</script>

<template>
    <header class="sticky top-0 z-40 w-full border-b bg-background/90 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="container mx-auto flex h-16 max-w-7xl items-center space-x-4 px-6 sm:justify-between sm:space-x-0 lg:px-8">
            <div class="flex gap-6 md:gap-10">
                <Link href="/" class="flex items-center space-x-2">
                    <Utensils class="h-6 w-6 text-primary" />
                    <span class="inline-block font-bold">NutriPlan</span>
                </Link>
                <nav class="hidden gap-6 md:flex">
                    <Link href="#features" class="flex items-center text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                        Features
                    </Link>
                    <Link
                        href="#testimonials"
                        class="flex items-center text-sm font-medium text-muted-foreground transition-colors hover:text-primary"
                    >
                        Testimonials
                    </Link>
                    <!-- <Link href="#"
                        class="flex items-center text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                    About
                    </Link>
                    <Link href="#"
                        class="flex items-center text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                    Contact
                    </Link> -->
                </nav>
            </div>
            <div v-if="!loggedIn" class="flex flex-1 items-center justify-end space-x-4">
                <nav class="flex items-center space-x-2">
                    <ModeToggle />
                    <Link href="/login">
                        <Button variant="ghost" size="sm"> Log in </Button>
                    </Link>
                    <Link href="/register">
                        <Button size="sm">Sign up</Button>
                    </Link>
                </nav>
            </div>
        </div>
    </header>
</template>
