<script setup lang="ts">
// import { cn } from '@/lib/utils' // Removed cn usage for now
</script>

<template>
    <td :class="[
        'p-4 align-middle [&:has([role=checkbox])]:pr-0',
        $attrs.class ?? '',
    ]">
        <slot />
    </td>
</template>