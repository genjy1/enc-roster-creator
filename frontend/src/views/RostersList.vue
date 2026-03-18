<template>
  <div class="max-w-7xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-3xl font-bold tracking-tight">Ростеры</h1>
      <RouterLink
        to="/roster/add"
        class="bg-brand hover:bg-brand-hover text-white text-sm font-semibold px-4 py-2 rounded transition-colors"
      >
        + Создать ростер
      </RouterLink>
    </div>

    <div v-if="loading" class="text-gray-500">Загрузка...</div>
    <div v-else-if="error" class="text-red-400">{{ error }}</div>

    <p v-else-if="!rosters.length" class="text-gray-500">
      Ростеры ещё не созданы.
      <RouterLink to="/roster/add" class="text-brand hover:underline">Создать первый</RouterLink>
    </p>

    <div v-else class="flex flex-col gap-6">
      <div
        v-for="roster in rosters"
        :key="roster.id"
        class="bg-card border border-border rounded-xl overflow-hidden"
      >
        <!-- Country header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
          <div class="flex items-center gap-3">
            <span :class="['fi', 'fi-' + roster.code.toLowerCase(), 'text-2xl']" />
            <div>
              <p class="font-bold text-lg">{{ roster.name }}</p>
              <p class="text-xs text-gray-500">
                {{ roster.players.length }} игрок{{ playerSuffix(roster.players.length) }}
              </p>
            </div>
          </div>
          <RouterLink
            :to="`/roster/add?country=${roster.code}`"
            class="text-xs text-gray-500 hover:text-brand transition-colors"
          >
            Редактировать
          </RouterLink>
        </div>

        <!-- Players grid -->
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          <div
            v-for="player in roster.players"
            :key="player.id"
            class="flex flex-col items-center gap-1.5 text-center"
          >
            <div
              class="w-14 h-14 rounded-full overflow-hidden bg-surface border border-border shrink-0"
            >
              <img
                :src="resolvePhoto(player.photo_url)"
                :alt="player.nickname"
                class="w-full h-full object-cover object-top"
              />
            </div>
            <p class="text-sm font-semibold text-brand truncate w-full">{{ player.nickname }}</p>
            <span class="text-xs text-gray-500 bg-surface rounded px-1.5 py-0.5">
              {{ player.position }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import type { RosterGroup } from '@/types/Api'
import { useApi } from '@/composables/useApi'
import { resolvePhoto } from '@/utils/resolvePhoto'
import { playerSuffix } from '@/utils/pluralRu'

const { loading, error, apiFetch } = useApi()
const rosters = ref<RosterGroup[]>([])

onMounted(async () => {
  rosters.value = (await apiFetch<RosterGroup[]>('/api/rosters')) ?? []
})
</script>
