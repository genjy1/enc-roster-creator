<template>
  <div class="max-w-7xl mx-auto px-6 py-10">

    <!-- Header -->
    <div class="flex items-end justify-between mb-8">
      <div>
        <p class="text-xs text-brand font-semibold uppercase tracking-widest mb-1">Составы команд</p>
        <h1 class="text-4xl font-black tracking-tight">Ростеры</h1>
      </div>
      <RouterLink to="/roster/add" class="btn-primary text-sm">
        + Создать ростер
      </RouterLink>
    </div>

    <!-- States -->
    <div v-if="loading" class="flex items-center gap-3 text-gray-500 py-20 justify-center">
      <span class="w-5 h-5 border-2 border-gray-700 border-t-brand rounded-full animate-spin" />
      Загрузка ростеров…
    </div>
    <div v-else-if="error" class="text-red-400 py-10 text-center">{{ error }}</div>

    <div v-else-if="!rosters.length" class="text-center py-20 text-gray-600">
      <div class="w-16 h-16 rounded-2xl bg-card border border-border flex items-center justify-center mx-auto mb-4">
        <ClipboardDocumentListIcon class="w-8 h-8 text-gray-600" />
      </div>
      <p class="mb-2">Ростеры ещё не созданы.</p>
      <RouterLink to="/roster/add" class="text-brand hover:underline text-sm">Создать первый</RouterLink>
    </div>

    <!-- Roster cards -->
    <div v-else class="flex flex-col gap-6">
      <div
        v-for="roster in rosters"
        :key="roster.id"
        class="bg-card border border-border rounded-2xl overflow-hidden"
      >
        <!-- Country header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-border">
          <div class="flex items-center gap-3">
            <span :class="['fi', 'fi-' + roster.code.toLowerCase(), 'text-2xl rounded shadow-sm']" />
            <div>
              <p class="font-bold text-lg leading-tight">{{ roster.name }}</p>
              <p class="text-xs text-gray-500">
                {{ roster.players.length }}&nbsp;игрок{{ playerSuffix(roster.players.length) }}
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
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          <RouterLink
            v-for="player in roster.players"
            :key="player.id"
            :to="`/player/${player.id}/edit`"
            class="group flex flex-col items-center gap-2 text-center p-2 rounded-xl hover:bg-white/[0.03] transition-colors"
          >
            <!-- Avatar -->
            <div class="relative w-16 h-16 rounded-full overflow-hidden bg-surface border border-border shrink-0 group-hover:border-border-bright transition-colors">
              <img
                :src="resolvePhoto(player.photo_url)"
                :alt="player.nickname"
                class="w-full h-full object-cover object-top"
              />
              <!-- Flag overlay -->
              <span
                v-if="player.country_code"
                :class="['fi', 'fi-' + player.country_code.toLowerCase(), 'absolute bottom-0 right-0 text-[10px] rounded-tl']"
              />
            </div>

            <!-- Nickname -->
            <p class="text-sm font-bold text-brand truncate w-full group-hover:text-amber-400 transition-colors">
              {{ player.nickname }}
            </p>

            <!-- Position badges -->
            <div class="flex flex-wrap justify-center gap-1 w-full">
              <span :class="['text-[10px] font-bold px-1.5 py-0.5 rounded-full', positionBadge(player.primary_position)]">
                {{ player.primary_position }}
              </span>
              <span
                v-if="player.secondary_position"
                :class="['text-[10px] font-semibold px-1.5 py-0.5 rounded-full opacity-70', positionBadge(player.secondary_position)]"
              >
                {{ player.secondary_position }}
              </span>
            </div>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'
import type { RosterGroup } from '@/types/Api'
import { useApi } from '@/composables/useApi'
import { positionBadge } from '@/utils/positionBadge'
import { resolvePhoto } from '@/utils/resolvePhoto'
import { playerSuffix } from '@/utils/pluralRu'

const { loading, error, apiFetch } = useApi()
const rosters = ref<RosterGroup[]>([])

onMounted(async () => {
  rosters.value = (await apiFetch<RosterGroup[]>('/api/rosters')) ?? []
})
</script>
