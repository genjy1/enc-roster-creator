<template>
  <div class="max-w-7xl mx-auto px-6 py-10">
    <h1 class="text-3xl font-bold tracking-tight mb-8">Страны</h1>

    <div v-if="loading" class="text-gray-500">Загрузка...</div>
    <div v-else-if="error" class="text-red-400">{{ error }}</div>

    <ul v-else class="flex flex-col gap-2">
      <li
        v-for="country in countries"
        :key="country.id"
        class="bg-card border border-border rounded-lg overflow-hidden"
      >
        <!-- Header -->
        <button
          class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-white/5 transition-colors cursor-pointer"
          @click="toggle(country.id)"
        >
          <span class="flex items-center gap-3 font-medium">
            <span :class="['fi', 'fi-' + country.code.toLowerCase(), 'text-lg']"></span>
            {{ country.name }}
            <span class="text-xs text-gray-500 font-normal">
              {{ country.players?.length ?? 0 }} игроков
            </span>
          </span>
          <ChevronDownIcon
            class="w-4 h-4 text-gray-500 transition-transform duration-200"
            :class="{ 'rotate-180': openIds.has(country.id) }"
          />
        </button>

        <!-- Players -->
        <div v-show="openIds.has(country.id)" class="border-t border-border px-5 py-4">

          <div
            v-if="country.players?.length"
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2"
          >
            <div
              v-for="player in country.players"
              :key="player.id"
              class="bg-surface rounded px-3 py-1.5 text-sm text-gray-300 truncate"
            >
              {{ player.nickname }}
            </div>
          </div>
          <p v-else class="text-sm text-gray-600">Нет игроков</p>
        </div>
      </li>
    </ul>
  </div>
</template>

<script lang="ts" setup>
import { onMounted, reactive, ref } from 'vue'
import { ChevronDownIcon } from '@heroicons/vue/24/outline'
import type { Country } from '@/types/Api'
import { useApi } from '@/composables/useApi'

const { loading, error, apiFetch } = useApi()

const countries = ref<Country[]>([])
const openIds = reactive(new Set<number>())

function toggle(id: number): void {
  if (openIds.has(id)) {
    openIds.delete(id)
  } else {
    openIds.add(id)
  }
}

onMounted(async () => {
  countries.value = (await apiFetch<Country[]>('/api/countries')) ?? []
})
</script>
