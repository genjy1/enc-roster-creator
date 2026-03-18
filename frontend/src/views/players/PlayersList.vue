<template>
  <div class="max-w-7xl mx-auto px-6 py-10">
    <!-- Header -->
    <div class="flex items-end justify-between mb-8">
      <div>
        <p class="text-xs text-brand font-semibold uppercase tracking-widest mb-1">База игроков</p>
        <h1 class="text-4xl font-black tracking-tight">Игроки</h1>
      </div>
      <span v-if="!loading" class="text-sm text-gray-600 mb-1">
        {{ filtered.length }} из {{ players.length }}
      </span>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap gap-3 mb-8 p-4 bg-card border border-border rounded-xl">
      <UniversalInput
        v-model="search"
        type="search"
        placeholder="Поиск по никнейму или имени…"
        class="w-72"
      />
      <AppSelect
        v-model="positionFilter"
        :options="positionOptions"
        placeholder="Все позиции"
        class="w-48"
      />
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="flex items-center gap-3 text-gray-500 py-20 justify-center">
      <span class="w-5 h-5 border-2 border-gray-700 border-t-brand rounded-full animate-spin" />
      Загрузка игроков…
    </div>
    <div v-else-if="error" class="text-red-400 py-10 text-center">{{ error }}</div>

    <!-- Grid -->
    <ul
      v-else-if="filtered.length"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
    >
      <li
        v-for="player in filtered"
        :key="player.id"
        class="bg-card border border-border rounded-xl overflow-hidden card-lift group"
        @click.prevent="navigateToPlayer(player.id)"
      >
        <!-- Photo -->
        <div class="relative aspect-square bg-surface-2 overflow-hidden">
          <img
            :src="resolvePhoto(player.photo_url)"
            :alt="player.nickname"
            class="w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-105"
          />
          <!-- Bottom gradient overlay -->
          <div
            class="absolute inset-x-0 bottom-0 h-1/2"
            style="background: linear-gradient(to top, #161a1f, transparent)"
          />
          <!-- Flag badge -->
          <span
            v-if="player.country_code"
            :class="[
              'fi',
              'fi-' + player.country_code.toLowerCase(),
              'absolute top-2 right-2 rounded shadow-lg text-base',
            ]"
            :title="player.country_name ?? undefined"
          />
        </div>

        <!-- Info -->
        <div class="p-3 flex flex-col gap-1.5">
          <p class="font-bold text-brand truncate text-sm">{{ player.nickname }}</p>
          <p class="text-xs text-gray-500 truncate">{{ player.name }} {{ player.surname }}</p>
          <div class="flex flex-wrap gap-1">
            <span
              :class="['text-xs font-medium px-2 py-0.5 rounded-full', positionBadge(player.primary_position)]"
            >
              {{ player.primary_position }}
            </span>
            <span
              v-if="player.secondary_position"
              :class="['text-xs font-medium px-2 py-0.5 rounded-full opacity-70', positionBadge(player.secondary_position)]"
            >
              {{ player.secondary_position }}
            </span>
          </div>
        </div>
      </li>
    </ul>

    <div v-else class="text-center py-20 text-gray-600">
      <div class="w-16 h-16 rounded-2xl bg-card border border-border flex items-center justify-center mx-auto mb-4">
        <MagnifyingGlassIcon class="w-8 h-8 text-gray-600" />
      </div>
      <p>Игроки не найдены</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import type { Player } from '@/types/Api'
import { useApi } from '@/composables/useApi'
import { resolvePhoto } from '@/utils/resolvePhoto'
import { positionBadge } from '@/utils/positionBadge'
import AppSelect from '@/components/AppSelect.vue'
import type { SelectOption } from '@/components/AppSelect.vue'
import UniversalInput from '@/components/UniversalInput.vue'
import { useRouter } from 'vue-router'

const { loading, error, apiFetch } = useApi()
const router = useRouter()

const players = ref<Player[]>([])
const search = ref('')
const positionFilter = ref('')

const positionOptions = computed<SelectOption[]>(() => {
  const all = players.value.flatMap((p) =>
    [p.primary_position, p.secondary_position].filter(Boolean),
  )
  return [...new Set(all)].sort().map((p) => ({ value: p, label: p }))
})

const filtered = computed<Player[]>(() => {
  const q = search.value.toLowerCase()
  return players.value.filter((p) => {
    const matchesSearch =
      !q ||
      p.nickname.toLowerCase().includes(q) ||
      p.name.toLowerCase().includes(q) ||
      p.surname.toLowerCase().includes(q)
    const matchesPosition =
      !positionFilter.value ||
      p.primary_position === positionFilter.value ||
      p.secondary_position === positionFilter.value
    return matchesSearch && matchesPosition
  })
})

const navigateToPlayer = (playerId: number) => {
  router.push({ name: 'PlayerEdit', params: { id: playerId } })
}

onMounted(async () => {
  players.value = (await apiFetch<Player[]>('/api/players')) ?? []
})
</script>
