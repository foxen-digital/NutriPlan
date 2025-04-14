<script setup lang="ts">
// import { cn } from '@/lib/utils' // Removed cn usage for now
</script>

<template>
    <tr :class="[
        'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
        $attrs.class ?? '',
    ]">
        <slot />
    </tr>
</template>