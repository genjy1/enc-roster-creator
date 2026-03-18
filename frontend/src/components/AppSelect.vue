<template>
  <div class="relative" ref="rootEl">

    <!-- Trigger -->
    <button
      type="button"
      class="flex items-center justify-between gap-3 w-full bg-card border text-sm px-4 py-2.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-brand"
      :class="open ? 'border-brand' : 'border-border hover:border-gray-500'"
      @click="toggle"
    >
      <!-- Selected option -->
      <span class="flex items-center gap-2 truncate" :class="!modelValue ? 'text-gray-500' : 'text-gray-200'">
        <slot name="prefix" :option="selectedOption" />
        {{ selectedOption?.label ?? placeholder }}
      </span>

      <!-- Chevron -->
      <ChevronDownIcon
        class="w-4 h-4 text-gray-500 shrink-0 transition-transform duration-200"
        :class="{ 'rotate-180': open }"
      />
    </button>

    <!-- Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="open"
        class="absolute z-50 mt-1 w-full bg-card border border-border rounded-lg shadow-xl overflow-hidden"
      >
        <!-- Search -->
        <div v-if="searchable" class="p-2 border-b border-border">
          <input
            ref="searchEl"
            v-model="query"
            type="text"
            placeholder="Поиск…"
            class="w-full bg-surface text-sm text-gray-200 placeholder-gray-600 px-3 py-1.5 rounded focus:outline-none"
          />
        </div>

        <!-- Options -->
        <ul class="max-h-60 overflow-y-auto py-1">
          <!-- Placeholder option -->
          <li
            v-if="placeholder"
            class="flex items-center gap-2 px-4 py-2 text-sm text-gray-500 hover:bg-white/5 cursor-pointer"
            :class="{ 'bg-white/5': modelValue === '' }"
            @click="select('')"
          >
            {{ placeholder }}
          </li>

          <!-- Filtered options -->
          <li
            v-for="option in filteredOptions"
            :key="option.value"
            class="flex items-center gap-2 px-4 py-2 text-sm cursor-pointer transition-colors"
            :class="
              option.value === modelValue
                ? 'bg-brand/10 text-brand'
                : 'text-gray-200 hover:bg-white/5'
            "
            @click="select(option.value)"
          >
            <slot name="option" :option="option">
              {{ option.label }}
            </slot>
            <CheckIcon
              v-if="option.value === modelValue"
              class="w-3.5 h-3.5 ml-auto shrink-0 text-brand"
            />
          </li>

          <li v-if="!filteredOptions.length" class="px-4 py-3 text-sm text-gray-600 text-center">
            Ничего не найдено
          </li>
        </ul>
      </div>
    </Transition>

  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { CheckIcon, ChevronDownIcon } from '@heroicons/vue/24/solid'

export interface SelectOption {
  value: string
  label: string
}

const props = withDefaults(
  defineProps<{
    modelValue: string
    options: SelectOption[]
    placeholder?: string
    searchable?: boolean
  }>(),
  {
    placeholder: '— Выбрать —',
    searchable: false,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const open = ref(false)
const query = ref('')
const rootEl = ref<HTMLElement | null>(null)
const searchEl = ref<HTMLInputElement | null>(null)

const selectedOption = computed(() =>
  props.options.find((o) => o.value === props.modelValue),
)

const filteredOptions = computed(() => {
  if (!query.value) return props.options
  const q = query.value.toLowerCase()
  return props.options.filter((o) => o.label.toLowerCase().includes(q))
})

function toggle(): void {
  open.value = !open.value
  if (open.value && props.searchable) {
    nextTick(() => searchEl.value?.focus())
  }
}

function select(value: string): void {
  emit('update:modelValue', value)
  open.value = false
  query.value = ''
}

onClickOutside(rootEl, () => {
  open.value = false
  query.value = ''
})

watch(open, (val) => {
  if (!val) query.value = ''
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
