<template>
  <select :value="modelValue"
    class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary sm:text-sm sm:leading-6"
    @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value === '' ? null : ($event.target as HTMLSelectElement).value)">
    <option v-if="allowEmpty" value="">None</option>
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string | null
  options: Array<{ value: string; label: string }>
  allowEmpty?: boolean
}>()

defineEmits<{
  (e: 'update:modelValue', value: string | null): void
}>()
</script>
