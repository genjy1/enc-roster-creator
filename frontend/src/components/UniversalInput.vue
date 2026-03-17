<template>
  <input :type="props.type" :name="props.name" :placeholder="props.placeholder" :value="props.modelValue" @input="emit('update:modelValue', $event.target.value)" class="border rounded px-4 py-2">
</template>
<script setup lang="ts">

const props = defineProps(['type', 'name', 'placeholder', 'modelValue'])
const emit = defineEmits(['update:modelValue'])

</script>
