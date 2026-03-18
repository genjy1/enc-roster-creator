<template>
  <!-- Page wrapper -->
  <div class="relative min-h-screen">

    <!-- Ambient grid background layer -->
    <div class="pointer-events-none fixed inset-0 grid-pattern opacity-40" />
    <!-- Top-left brand radial glow -->
    <div
      class="pointer-events-none fixed top-0 left-0 w-[600px] h-[400px] opacity-20"
      style="background: radial-gradient(ellipse at 0% 0%, rgb(244 151 26 / 0.35) 0%, transparent 70%)"
    />

    <div class="relative max-w-7xl mx-auto px-6 pt-10 pb-40">

      <!-- ── Page header ── -->
      <header class="mb-10">
        <p class="text-xs font-bold tracking-[0.2em] uppercase text-gradient mb-2">
          Формирование
        </p>
        <div class="flex items-end justify-between gap-4 flex-wrap">
          <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white leading-none">
            Создать&nbsp;<span class="text-gradient">ростер</span>
          </h1>
          <!-- Player counter pill -->
          <div
            v-if="selectedCode"
            class="flex items-center gap-2 bg-card border border-border rounded-full px-4 py-1.5 text-sm"
          >
            <span class="w-2 h-2 rounded-full bg-brand animate-pulse" />
            <span class="text-gray-400">Выбрано:</span>
            <span class="font-bold text-white">{{ roster.length }}</span>
            <span class="text-gray-600">/</span>
            <span class="text-gray-400">{{ countryPlayers.length }}</span>
          </div>
        </div>
        <!-- Decorative divider -->
        <div class="mt-6 h-px bg-gradient-to-r from-brand/40 via-border to-transparent" />
      </header>

      <!-- ── Country selector row ── -->
      <section class="mb-10">
        <p class="text-xs font-semibold tracking-widest uppercase text-gray-500 mb-3">
          01 — Страна
        </p>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">

          <!-- Dropdown -->
          <AppSelect
            v-model="selectedCode"
            :options="countryOptions"
            placeholder="— Выберите страну —"
            :searchable="true"
            class="w-full max-w-xs"
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

          <!-- Animated selected country card -->
          <Transition name="fade-slide">
            <div
              v-if="selectedCountry"
              class="flex items-center gap-3 bg-card border border-border-bright rounded-xl px-4 py-2.5 glow-brand-sm"
            >
              <span
                :class="['fi', 'fi-' + selectedCountry.code.toLowerCase(), 'text-2xl', 'rounded-sm', 'shadow-md']"
              />
              <div class="leading-tight">
                <p class="text-sm font-bold text-white">{{ selectedCountry.name }}</p>
                <p class="text-xs text-gray-500">
                  {{ countryPlayers.length }}&nbsp;игрок{{ playerSuffix(countryPlayers.length) }}
                </p>
              </div>
            </div>
          </Transition>

        </div>
      </section>

      <!-- ── Players section ── -->
      <section>
        <p class="text-xs font-semibold tracking-widest uppercase text-gray-500 mb-4">
          02 — Состав
        </p>

        <!-- Populated grid -->
        <TransitionGroup
          v-if="countryPlayers.length"
          tag="ul"
          name="grid-item"
          class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
        >
          <li v-for="player in countryPlayers" :key="player.id">
            <PlayerCard
              :player="player"
              :in-roster="inRoster(player.id)"
              @toggle="toggleRoster"
            />
          </li>
        </TransitionGroup>

        <!-- Empty state — country chosen but no players -->
        <Transition name="fade-slide">
          <div
            v-if="selectedCode && !countryPlayers.length"
            class="flex flex-col items-center justify-center gap-4 py-24 text-center"
          >
            <div class="w-16 h-16 rounded-2xl bg-card border border-border flex items-center justify-center opacity-50">
              <UserGroupIcon class="w-8 h-8 text-gray-500" />
            </div>
            <p class="text-gray-500 text-sm">Нет игроков для этой страны.</p>
          </div>
        </Transition>

        <!-- Idle state — no country chosen yet -->
        <Transition name="fade-slide">
          <div
            v-if="!selectedCode"
            class="flex flex-col items-center justify-center gap-4 py-24 text-center"
          >
            <div class="w-16 h-16 rounded-2xl bg-card border border-border flex items-center justify-center opacity-40">
              <GlobeEuropeAfricaIcon class="w-8 h-8 text-gray-500" />
            </div>
            <p class="text-gray-600 text-sm">Выберите страну, чтобы увидеть игроков</p>
          </div>
        </Transition>

      </section>
    </div>
  </div>

  <!-- ── Sticky roster bar ── -->
  <Transition name="slide-up">
    <div
      v-if="roster.length"
      class="fixed bottom-0 left-0 right-0 z-40"
      style="background: rgb(12 13 15 / 0.75); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-top: 1px solid rgb(37 42 48 / 0.8);"
    >
      <!-- Top accent line -->
      <div class="h-px w-full bg-gradient-to-r from-transparent via-brand/50 to-transparent" />

      <div class="max-w-7xl mx-auto flex items-center justify-between gap-4 px-6 py-4">

        <!-- Avatar stack + count -->
        <div class="flex items-center gap-3 min-w-0">
          <div class="flex -space-x-2.5 shrink-0">
            <img
              v-for="player in roster.slice(0, 7)"
              :key="player.id"
              :src="resolvePhoto(player.photo_url)"
              :alt="player.nickname"
              :title="player.nickname"
              class="w-9 h-9 rounded-full object-cover object-top border-2 border-[rgb(12_13_15)]"
              style="box-shadow: 0 0 0 1px rgb(37 42 48);"
            />
            <div
              v-if="roster.length > 7"
              class="w-9 h-9 rounded-full bg-surface-2 border-2 border-[rgb(12_13_15)] flex items-center justify-center text-xs font-bold text-gray-400"
              style="box-shadow: 0 0 0 1px rgb(37 42 48);"
            >
              +{{ roster.length - 7 }}
            </div>
          </div>
          <div class="leading-tight min-w-0">
            <p class="text-white font-bold text-sm tabular-nums">
              {{ roster.length }}&nbsp;<span class="text-gray-500 font-normal">игрок{{ rosterSuffix }}</span>
            </p>
            <p class="text-xs text-gray-600 truncate">в ростере</p>
          </div>
        </div>

        <!-- Feedback message -->
        <Transition name="fade-slide">
          <div v-if="saveError" class="flex items-center gap-1.5 text-sm text-red-400 flex-1 justify-center">
            <span class="text-base leading-none">⚠</span>
            <span>{{ saveError }}</span>
          </div>
          <div v-else-if="saveSuccess" class="flex items-center gap-1.5 text-sm text-green-400 flex-1 justify-center">
            <span class="text-base leading-none">✓</span>
            <span>Ростер сохранён</span>
          </div>
        </Transition>

        <!-- Actions -->
        <div class="flex items-center gap-3 shrink-0">
          <button
            class="btn-ghost text-sm disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="saving"
            @click="clearRoster"
          >
            Очистить
          </button>
          <button
            class="btn-primary text-sm disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="saving || !selectedCountry"
            :title="!selectedCountry ? 'Выберите страну перед сохранением' : ''"
            @click="saveRoster"
          >
            <span
              v-if="saving"
              class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin"
            />
            <CloudArrowUpIcon v-else class="w-4 h-4" />
            {{ saving ? 'Сохранение…' : 'Сохранить ростер' }}
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script lang="ts" setup>
import { computed, onMounted, ref } from 'vue'
import { CloudArrowUpIcon, GlobeEuropeAfricaIcon, UserGroupIcon } from '@heroicons/vue/24/outline'
import type { Country, Player } from '@/types/Api'
import { useApi } from '@/composables/useApi'
import { resolvePhoto } from '@/utils/resolvePhoto'
import { playerSuffix } from '@/utils/pluralRu'
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

const rosterSuffix = computed(() => playerSuffix(roster.value.length))

onMounted(async () => {
  countries.value = (await apiFetch<Country[]>('/api/countries')) ?? []
})
</script>

<style scoped>
/* ── Sticky bar slide-up ── */
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

/* ── Fade + slide-up for inline elements ── */
.fade-slide-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.fade-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* ── Player grid staggered enter ── */
.grid-item-enter-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.grid-item-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.grid-item-enter-from,
.grid-item-leave-to {
  opacity: 0;
  transform: translateY(12px) scale(0.97);
}
.grid-item-move {
  transition: transform 0.3s ease;
}
</style>
