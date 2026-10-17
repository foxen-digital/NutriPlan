<template>
  <div>
    <input
      ref="fileInput"
      type="file"
      class="hidden"
      :accept="accept"
      :multiple="multiple"
      @change="handleFileChange"
    />
    <div
      class="flex flex-col items-center justify-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md appearance-none cursor-pointer hover:border-gray-400 focus:outline-none"
      @click="fileInput?.click()"
      @dragover.prevent="dragover = true"
      @dragleave.prevent="dragover = false"
      @drop.prevent="handleDrop"
    >
      <span class="flex items-center space-x-2">
        <UploadIcon class="w-6 h-6 text-gray-600" />
        <span class="font-medium text-gray-600">
          Drop files to upload, or
          <span class="text-blue-600 underline">browse</span>
        </span>
      </span>
      <span class="text-xs text-gray-500">
        {{ multiple ? 'Upload multiple files' : 'Upload a file' }}
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { UploadIcon } from 'lucide-vue-next'

interface Props {
  modelValue: File | File[]
  accept?: string
  multiple?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  accept: '',
  multiple: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: File | File[]): void
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const dragover = ref(false)

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (!target.files?.length) return

  emit('update:modelValue', props.multiple ? Array.from(target.files) : target.files[0])

  // Clear the selection so picking the same file again fires another change event.
  target.value = ''
}

const handleDrop = (event: DragEvent) => {
  dragover.value = false
  if (!event.dataTransfer?.files.length) return

  emit('update:modelValue', props.multiple ? Array.from(event.dataTransfer.files) : event.dataTransfer.files[0])
}
</script>
