<script setup lang="ts">
// import { cn } from '@/lib/utils' // Removed cn usage for now
</script>

<template>
    <tbody :class="('[&_tr:last-child]:border-0', $attrs.class ?? '')">
        <slot />
    </tbody>
</template>