<template>
    <footer className="w-full border-t py-2 md:py-0">
        <div className="container flex flex-col items-center justify-between gap-4 md:h-16 md:flex-row mx-auto max-w-7xl px-6 lg:px-8">
            <div className="flex items-center gap-2">
                <Utensils className="h-5 w-5 text-primary" />
                <p className="text-sm leading-loose text-muted-foreground md:text-left">&copy; NutriPlan. All rights reserved.</p>
            </div>
            <div className="flex gap-4">
                <!-- <Link href="#"
                    className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Terms
                </Link>
                <Link href="#"
                    className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Privacy
                </Link>
                <Link href="#"
                    className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Contact
                </Link> -->
            </div>
        </div>
    </footer>
</template>

<script lang="ts" setup>
import { Utensils } from 'lucide-vue-next';
</script>
