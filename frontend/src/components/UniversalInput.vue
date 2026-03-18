<template>
  <input
    :type="props.type"
    :name="props.name"
    :placeholder="props.placeholder"
    :value="props.modelValue"
    class="w-full bg-card border border-border text-gray-200 placeholder-gray-600 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand transition"
    @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
  />
</template>

<script setup lang="ts">
const props = defineProps<{
  type?: string
  name?: string
  placeholder?: string
  modelValue?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
