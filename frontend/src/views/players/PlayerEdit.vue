<template>
  <!-- Loading -->
  <div v-if="loading && !player" class="flex items-center gap-3 text-gray-500 py-32 justify-center">
    <span class="w-5 h-5 border-2 border-gray-700 border-t-brand rounded-full animate-spin" />
    Загрузка…
  </div>

  <!-- Error -->
  <div v-else-if="error && !player" class="text-center py-32 text-red-400">{{ error }}</div>

  <!-- Form -->
  <div v-else-if="player" class="relative min-h-screen">
    <!-- Background decoration -->
    <div class="pointer-events-none fixed inset-0 grid-pattern opacity-40" />
    <div
      class="pointer-events-none fixed top-0 right-0 w-[500px] h-[400px] opacity-15"
      style="
        background: radial-gradient(ellipse at 100% 0%, rgb(244 151 26 / 0.35) 0%, transparent 70%);
      "
    />

    <div class="relative max-w-5xl mx-auto px-6 pt-10 pb-20">
      <!-- Header -->
      <header class="mb-10">
        <button
          class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-300 transition-colors mb-6 group"
          @click="router.back()"
        >
          <ChevronLeftIcon class="w-3.5 h-3.5 group-hover:-translate-x-0.5 transition-transform" />
          Назад к списку
        </button>

        <p class="text-xs font-bold tracking-[0.2em] uppercase text-gradient mb-2">
          Редактирование
        </p>
        <div class="flex items-end justify-between gap-4 flex-wrap">
          <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white leading-none">
            <span class="text-gradient">{{ player.nickname }}</span>
          </h1>
          <span
            v-if="player.country_code"
            :class="[
              'fi',
              'fi-' + player.country_code.toLowerCase(),
              'text-4xl rounded shadow-md shrink-0',
            ]"
            :title="player.country_name ?? undefined"
          />
        </div>
        <div class="mt-6 h-px bg-gradient-to-r from-brand/40 via-border to-transparent" />
      </header>

      <!-- Body: photo + form -->
      <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 items-start">
        <!-- Photo column -->
        <div class="flex flex-col gap-4">
          <div
            class="relative aspect-3/4 rounded-2xl overflow-hidden bg-surface-2 border border-border shadow-xl"
          >
            <img
              :src="resolvePhoto(form.photo_url || player.photo_url)"
              :alt="player.nickname"
              class="w-full h-full object-cover object-top"
            />
            <div
              class="absolute inset-x-0 bottom-0 h-2/5"
              style="background: linear-gradient(to top, #0c0d0f, transparent)"
            />
            <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between">
              <span
                :class="[
                  'text-xs font-bold px-2.5 py-1 rounded-full',
                  positionBadge(form.position || player.position),
                ]"
              >
                {{ form.position || player.position }}
              </span>
              <span
                v-if="player.country_code"
                :class="[
                  'fi',
                  'fi-' + player.country_code.toLowerCase(),
                  'text-xl rounded shadow-md',
                ]"
              />
            </div>
          </div>

          <div>
            <label class="field-label">URL фото</label>
            <UniversalInput v-model="form.photo_url" placeholder="https://…" />
          </div>
        </div>

        <!-- Form column -->
        <form class="flex flex-col gap-6" @submit.prevent="save">
          <div class="bg-card border border-border rounded-2xl p-6 flex flex-col gap-5">
            <p class="text-xs font-semibold tracking-widest uppercase text-gray-500">
              01 — Личные данные
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="field-label">Имя</label>
                <UniversalInput v-model="form.name" placeholder="Aleksandr" />
                <p v-if="fieldErrors.name" class="mt-1.5 text-xs text-red-400">
                  {{ fieldErrors.name }}
                </p>
              </div>
              <div>
                <label class="field-label">Фамилия</label>
                <UniversalInput v-model="form.surname" placeholder="Kostyliev" />
                <p v-if="fieldErrors.surname" class="mt-1.5 text-xs text-red-400">
                  {{ fieldErrors.surname }}
                </p>
              </div>
            </div>

            <div>
              <label class="field-label">Никнейм</label>
              <UniversalInput v-model="form.nickname" placeholder="s1mple" />
              <p v-if="fieldErrors.nickname" class="mt-1.5 text-xs text-red-400">
                {{ fieldErrors.nickname }}
              </p>
            </div>

            <div>
              <label class="field-label">Дата рождения</label>
              <AppDatePicker v-model="form.date_of_birth" placeholder="Выберите дату рождения" />
              <p v-if="fieldErrors.date_of_birth" class="mt-1.5 text-xs text-red-400">
                {{ fieldErrors.date_of_birth }}
              </p>
            </div>
          </div>

          <div class="bg-card border border-border rounded-2xl p-6 flex flex-col gap-5">
            <p class="text-xs font-semibold tracking-widest uppercase text-gray-500">
              02 — Роль и страна
            </p>

            <div>
              <label class="field-label">Позиция</label>
              <AppSelect
                v-model="form.position"
                :options="positionOptions"
                placeholder="— Выбрать позицию —"
              >
                <template #option="{ option }">
                  <span
                    :class="[
                      'text-xs font-bold px-2 py-0.5 rounded-full',
                      positionBadge(option.value),
                    ]"
                  >
                    {{ option.label }}
                  </span>
                </template>
                <template #prefix="{ option }">
                  <span
                    v-if="option"
                    :class="[
                      'text-xs font-bold px-2 py-0.5 rounded-full',
                      positionBadge(option.value),
                    ]"
                  >
                    {{ option.label }}
                  </span>
                </template>
              </AppSelect>
              <p v-if="fieldErrors.position" class="mt-1.5 text-xs text-red-400">
                {{ fieldErrors.position }}
              </p>
            </div>

            <div>
              <label class="field-label">Страна</label>
              <AppSelect
                v-model="form.country_id"
                :options="countryOptions"
                placeholder="— Выбрать страну —"
                :searchable="true"
              >
                <template #prefix="{ option }">
                  <span v-if="option" :class="['fi', 'fi-' + option.value.toLowerCase()]" />
                </template>
                <template #option="{ option }">
                  <span :class="['fi', 'fi-' + option.value.toLowerCase()]" />
                  {{ option.label }}
                </template>
              </AppSelect>
              <p v-if="fieldErrors.country_id" class="mt-1.5 text-xs text-red-400">
                {{ fieldErrors.country_id }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between gap-4">
            <Transition name="fade-slide">
              <p v-if="saveSuccess" class="text-sm text-green-400 flex items-center gap-1.5">
                <CheckCircleIcon class="w-4 h-4" />
                Изменения сохранены
              </p>
              <p v-else-if="saveError" class="text-sm text-red-400 flex items-center gap-1.5">
                <ExclamationCircleIcon class="w-4 h-4" />
                {{ saveError }}
              </p>
            </Transition>

            <button type="submit" class="btn-primary ml-auto" :disabled="saving">
              <span
                v-if="saving"
                class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin"
              />
              <CheckIcon v-else class="w-4 h-4" />
              {{ saving ? 'Сохранение…' : 'Сохранить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import { CheckIcon, ChevronLeftIcon } from '@heroicons/vue/24/solid'
import AppDatePicker from '@/components/AppDatePicker.vue'
import AppSelect from '@/components/AppSelect.vue'
import type { SelectOption } from '@/components/AppSelect.vue'
import UniversalInput from '@/components/UniversalInput.vue'
import { useApi } from '@/composables/useApi'
import type { Country, Player, PlayerPosition } from '@/types/Api'
import { positionBadge } from '@/utils/positionBadge'
import { resolvePhoto } from '@/utils/resolvePhoto'

const POSITIONS: PlayerPosition[] = ['AWPer', 'IGL', 'Rifler', 'Support', 'Lurker', 'Entry Fragger']

const route = useRoute()
const router = useRouter()
const playerId = route.params.id as string

const { loading, error, apiFetch: fetchPlayer } = useApi()
const { apiFetch: fetchCountries } = useApi()
const { apiFetch: putPlayer, loading: saving, error: saveError, fieldErrors } = useApi()

const player = ref<Player | null>(null)
const countries = ref<Country[]>([])
const saveSuccess = ref(false)

const form = ref({
  nickname: '',
  name: '',
  surname: '',
  date_of_birth: '',
  position: '',
  country_id: '',
  photo_url: '',
})

const positionOptions = computed<SelectOption[]>(() =>
  POSITIONS.map((p) => ({ value: p, label: p })),
)

const countryOptions = computed<SelectOption[]>(() =>
  countries.value.map((c) => ({ value: c.code, label: c.name })),
)

function populateForm(p: Player): void {
  form.value = {
    nickname: p.nickname,
    name: p.name,
    surname: p.surname,
    date_of_birth: p.date_of_birth ?? '',
    position: p.position,
    country_id: p.country_code ?? '',
    photo_url: p.photo_url ?? '',
  }
}

async function save(): Promise<void> {
  if (saving.value || !player.value) return

  saveSuccess.value = false

  const country = countries.value.find((c) => c.code === form.value.country_id)

  const updated = await putPlayer<Player>(`/api/player/${player.value.id}`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nickname: form.value.nickname,
      name: form.value.name,
      surname: form.value.surname,
      date_of_birth: form.value.date_of_birth,
      position: form.value.position,
      country_id: country?.id,
      photo_url: form.value.photo_url || null,
    }),
  })

  if (saveError.value) return

  if (updated) {
    player.value = updated
    populateForm(updated)
    saveSuccess.value = true
    setTimeout(() => (saveSuccess.value = false), 3000)
  }
}

onMounted(async () => {
  const [p, c] = await Promise.all([
    fetchPlayer<Player>(`/api/player/${playerId}`),
    fetchCountries<Country[]>('/api/countries'),
  ])

  if (p) {
    player.value = p
    populateForm(p)
  }
  if (c) {
    countries.value = c
  }
})
</script>

<style scoped>
.field-label {
  @reference '../../main.css';
  display: block;
  margin-bottom: 0.375rem;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--color-gray-500, #6b7280);
}

.fade-slide-enter-active {
  transition:
    opacity 0.25s ease,
    transform 0.25s ease;
}
.fade-slide-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(6px);
}
</style>
