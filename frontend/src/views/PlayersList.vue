<template>
  <div class="max-w-7xl mx-auto px-6 py-10">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-3xl font-bold tracking-tight">Игроки</h1>
      <span v-if="!loading" class="text-sm text-gray-500">{{ filtered.length }} игроков</span>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-8">
      <input
        v-model="search"
        type="search"
        placeholder="Поиск по никнейму или имени…"
        class="bg-card border border-border text-gray-200 placeholder-gray-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand w-72"
      />
      <AppSelect
        v-model="positionFilter"
        :options="positionOptions"
        placeholder="Все позиции"
        class="w-44"
      />
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="text-gray-500">Загрузка...</div>
    <div v-else-if="error" class="text-red-400">{{ error }}</div>

    <!-- Grid -->
    <ul
      v-else-if="filtered.length"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
    >
      <li
        v-for="player in filtered"
        :key="player.id"
        class="bg-card border border-border rounded-xl overflow-hidden hover:border-brand/50 transition-colors"
      >
        <!-- Photo -->
        <div class="aspect-square bg-surface overflow-hidden">
          <img
            :src="resolvePhoto(player.photo_url)"
            :alt="player.nickname"
            class="w-full h-full object-cover object-top"
          />
        </div>

        <!-- Info -->
        <div class="p-3 flex flex-col gap-1">
          <p class="font-bold text-brand truncate">{{ player.nickname }}</p>
          <p class="text-xs text-gray-400 truncate">{{ player.name }} {{ player.surname }}</p>
          <div class="flex items-center justify-between gap-1 mt-0.5">
            <span class="text-xs bg-surface text-gray-500 rounded px-1.5 py-0.5 truncate">
              {{ player.position }}
            </span>
            <span
              v-if="player.country_code"
              :class="['fi', 'fi-' + player.country_code.toLowerCase()]"
              :title="player.country_name"
            />
          </div>
        </div>
      </li>
    </ul>

    <p v-else class="text-gray-500">Игроки не найдены.</p>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useApi } from '@/composables/useApi'
import { resolvePhoto } from '@/utils/resolvePhoto'
import AppSelect from '@/components/AppSelect.vue'
import type { SelectOption } from '@/components/AppSelect.vue'

interface PlayerRow {
  id: number
  nickname: string
  name: string
  surname: string
  date_of_birth: string
  position: string
  photo_url: string | null
  country_code: string | null
  country_name: string | undefined
}

const { loading, error, apiFetch } = useApi()

const players = ref<PlayerRow[]>([])
const search = ref('')
const positionFilter = ref('')

const positionOptions = computed<SelectOption[]>(() =>
  [...new Set(players.value.map((p) => p.position).filter(Boolean))]
    .sort()
    .map((p) => ({ value: p, label: p })),
)

const filtered = computed<PlayerRow[]>(() => {
  const q = search.value.toLowerCase()
  return players.value.filter((p) => {
    const matchesSearch =
      !q ||
      p.nickname.toLowerCase().includes(q) ||
      p.name.toLowerCase().includes(q) ||
      p.surname.toLowerCase().includes(q)
    const matchesPosition = !positionFilter.value || p.position === positionFilter.value
    return matchesSearch && matchesPosition
  })
})

onMounted(async () => {
  players.value = (await apiFetch<PlayerRow[]>('/api/players')) ?? []
})
</script>
