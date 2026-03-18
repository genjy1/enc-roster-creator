<template>
  <div class="max-w-7xl mx-auto px-6 py-10 pb-32">
    <h1 class="text-3xl font-bold tracking-tight mb-8">Создать ростер</h1>

    <!-- Country selector -->
    <div class="mb-10">
      <label class="block text-sm font-medium text-gray-400 mb-2">Выберите страну</label>
      <AppSelect
        v-model="selectedCode"
        :options="countryOptions"
        placeholder="— Все страны —"
        :searchable="true"
        class="max-w-sm"
      >
        <template #prefix="{ option }">
          <span
            v-if="option"
            :class="['fi', 'fi-' + option.value.toLowerCase()]"
          />
        </template>
        <template #option="{ option }">
          <span :class="['fi', 'fi-' + option.value.toLowerCase()]" />
          {{ option.label }}
        </template>
      </AppSelect>
    </div>

    <!-- Players grid -->
    <ul
      v-if="countryPlayers.length"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
    >
      <li v-for="player in countryPlayers" :key="player.id">
        <PlayerCard
          :player="player"
          :in-roster="inRoster(player.id)"
          @toggle="toggleRoster"
        />
      </li>
    </ul>

    <p v-else-if="selectedCode" class="text-gray-500">Нет игроков для этой страны.</p>
  </div>

  <!-- Sticky roster bar -->
  <Transition name="slide-up">
    <div
      v-if="roster.length"
      class="fixed bottom-0 left-0 right-0 bg-card border-t border-border px-6 py-4 z-40"
    >
      <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">

        <!-- Avatars + count -->
        <div class="flex items-center gap-3">
          <div class="flex -space-x-2">
            <img
              v-for="player in roster.slice(0, 8)"
              :key="player.id"
              :src="resolvePhoto(player.photo_url)"
              :alt="player.nickname"
              :title="player.nickname"
              class="w-9 h-9 rounded-full object-cover object-top border-2 border-card"
            />
            <div
              v-if="roster.length > 8"
              class="w-9 h-9 rounded-full bg-surface border-2 border-card flex items-center justify-center text-xs text-gray-400"
            >
              +{{ roster.length - 8 }}
            </div>
          </div>
          <span class="text-sm text-gray-400">
            <span class="text-white font-semibold">{{ roster.length }}</span>
            {{ ' ' }}игрок{{ rosterSuffix }}
          </span>
        </div>

        <!-- Feedback -->
        <p v-if="saveError" class="text-sm text-red-400 flex-1 text-center">{{ saveError }}</p>
        <p v-else-if="saveSuccess" class="text-sm text-green-400 flex-1 text-center">
          ✓ Ростер сохранён
        </p>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <button
            class="text-sm text-gray-500 hover:text-gray-300 transition-colors"
            :disabled="saving"
            @click="clearRoster"
          >
            Очистить
          </button>
          <button
            class="bg-brand hover:bg-brand-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2 rounded transition-colors flex items-center gap-2"
            :disabled="saving || !selectedCountry"
            :title="!selectedCountry ? 'Выберите страну перед сохранением' : ''"
            @click="saveRoster"
          >
            <span
              v-if="saving"
              class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"
            />
            {{ saving ? 'Сохранение…' : 'Сохранить ростер' }}
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import type { Country, Player } from '@/types/Api'
import { useApi } from '@/composables/useApi'
import { resolvePhoto } from '@/utils/resolvePhoto'
import PlayerCard from '@/components/PlayerCard.vue'
import AppSelect from '@/components/AppSelect.vue'
import type { SelectOption } from '@/components/AppSelect.vue'

const { apiFetch } = useApi()
const { apiFetch: postRoster, loading: saving, error: saveError } = useApi()

const countries = ref<Country[]>([])
const selectedCode = ref<string>('')
const roster = ref<Player[]>([])
const saveSuccess = ref(false)

const countryOptions = computed<SelectOption[]>(() =>
  countries.value.map((c) => ({ value: c.code, label: c.name })),
)

const selectedCountry = computed<Country | undefined>(() =>
  countries.value.find((c) => c.code === selectedCode.value),
)

const countryPlayers = computed<Player[]>(() => selectedCountry.value?.players ?? [])

function inRoster(id: number): boolean {
  return roster.value.some((p) => p.id === id)
}

function toggleRoster(player: Player): void {
  roster.value = inRoster(player.id)
    ? roster.value.filter((p) => p.id !== player.id)
    : [...roster.value, player]
  saveError.value = null
  saveSuccess.value = false
}

function clearRoster(): void {
  roster.value    = []
  saveError.value = null
  saveSuccess.value = false
}

async function saveRoster(): Promise<void> {
  if (!selectedCountry.value || saving.value) return

  saveSuccess.value = false

  await postRoster('/api/roster', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      country_id: selectedCountry.value.id,
      player_ids: roster.value.map((p) => p.id),
    }),
  })

  if (!saveError.value) {
    saveSuccess.value = true
    setTimeout(() => (saveSuccess.value = false), 3000)
  }
}

const rosterSuffix = computed<string>(() => {
  const n = roster.value.length % 10
  const n100 = roster.value.length % 100
  if (n100 >= 11 && n100 <= 14) return 'ов'
  if (n === 1) return ''
  if (n >= 2 && n <= 4) return 'а'
  return 'ов'
})

onMounted(async () => {
  countries.value = (await apiFetch<Country[]>('/api/countries')) ?? []
})
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition:
    transform 0.25s ease,
    opacity 0.25s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
