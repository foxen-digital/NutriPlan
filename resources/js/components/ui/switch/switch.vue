<template>
    <SwitchRoot :checked="checked"
        class="peer inline-flex h-[24px] w-[44px] shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input"
        :class="className" v-bind="$attrs" @update:checked="$emit('update:checked', $event)">
        <SwitchThumb
            class="pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0" />
    </SwitchRoot>
</template>

<script setup lang="ts">
import { SwitchRoot, SwitchThumb } from 'radix-vue';
import { cn } from '@/lib/utils';
import { computed } from 'vue';

const props = defineProps<{
    checked?: boolean;
    className?: string;
}>();

const className = computed(() => {
    return cn(props.className);
});

defineEmits<{
    'update:checked': [value: boolean];
}>();
</script>