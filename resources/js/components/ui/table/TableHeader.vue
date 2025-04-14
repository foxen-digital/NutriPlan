<script setup lang="ts">
// import { cn } from '@/lib/utils' // Removed cn usage for now
</script>

<template>
    <thead :class="('[&_tr]:border-b', $attrs.class ?? '')">
        <slot />
    </thead>
</template>