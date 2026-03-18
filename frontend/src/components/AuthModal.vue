<template>
  <Teleport to="body">
    <Transition name="modal-fade">
      <div
        v-if="open"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @mousedown.self="$emit('close')"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" />

        <!-- Panel -->
        <div class="relative w-full max-w-md bg-card border border-border rounded-2xl shadow-2xl overflow-hidden">

          <!-- Top accent line -->
          <div class="h-0.5 bg-gradient-to-r from-brand via-amber-400 to-transparent" />

          <!-- Header -->
          <div class="px-6 pt-6 pb-4 flex items-start justify-between">
            <div>
              <p class="text-xs font-bold tracking-[0.2em] uppercase text-brand mb-1">ENC</p>
              <h2 class="text-2xl font-black tracking-tight text-white">
                {{ tab === 'login' ? 'Вход' : 'Регистрация' }}
              </h2>
            </div>
            <button
              class="text-gray-600 hover:text-gray-300 transition-colors mt-1"
              @click="$emit('close')"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>
          </div>

          <!-- Tabs -->
          <div class="px-6 mb-6 flex gap-1 bg-surface mx-6 rounded-lg p-1">
            <button
              class="flex-1 text-sm font-semibold py-1.5 rounded-md transition-all"
              :class="tab === 'login' ? 'bg-card text-white shadow' : 'text-gray-500 hover:text-gray-300'"
              @click="switchTab('login')"
            >
              Войти
            </button>
            <button
              class="flex-1 text-sm font-semibold py-1.5 rounded-md transition-all"
              :class="tab === 'register' ? 'bg-card text-white shadow' : 'text-gray-500 hover:text-gray-300'"
              @click="switchTab('register')"
            >
              Создать аккаунт
            </button>
          </div>

          <!-- Form -->
          <form class="px-6 pb-6 flex flex-col gap-4" @submit.prevent="submit">

            <!-- Name (register only) -->
            <Transition name="field-slide">
              <div v-if="tab === 'register'" class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Имя</label>
                <input
                  v-model="form.name"
                  type="text"
                  placeholder="Ваше имя"
                  autocomplete="name"
                  class="input-field"
                  :class="{ 'border-red-500/60': fieldError('name') }"
                />
                <p v-if="fieldError('name')" class="text-xs text-red-400">{{ fieldError('name') }}</p>
              </div>
            </Transition>

            <!-- Email -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="you@example.com"
                autocomplete="email"
                class="input-field"
                :class="{ 'border-red-500/60': fieldError('email') }"
              />
              <p v-if="fieldError('email')" class="text-xs text-red-400">{{ fieldError('email') }}</p>
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-1.5">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Пароль</label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  autocomplete="current-password"
                  class="input-field pr-10"
                  :class="{ 'border-red-500/60': fieldError('password') }"
                />
                <button
                  type="button"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                  @click="showPassword = !showPassword"
                >
                  <EyeSlashIcon v-if="showPassword" class="w-4 h-4" />
                  <EyeIcon v-else class="w-4 h-4" />
                </button>
              </div>
              <p v-if="fieldError('password')" class="text-xs text-red-400">{{ fieldError('password') }}</p>
            </div>

            <!-- Confirm password (register only) -->
            <Transition name="field-slide">
              <div v-if="tab === 'register'" class="flex flex-col gap-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">
                  Подтвердите пароль
                </label>
                <input
                  v-model="form.passwordConfirmation"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  autocomplete="new-password"
                  class="input-field"
                  :class="{ 'border-red-500/60': fieldError('password_confirmation') }"
                />
              </div>
            </Transition>

            <!-- Global error -->
            <p v-if="globalError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/20 rounded-lg px-3 py-2">
              {{ globalError }}
            </p>

            <!-- Submit -->
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full py-2.5 mt-1 justify-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span
                v-if="loading"
                class="w-4 h-4 border-2 border-black/30 border-t-black rounded-full animate-spin"
              />
              {{ tab === 'login' ? 'Войти' : 'Создать аккаунт' }}
            </button>

          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { EyeIcon, EyeSlashIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useUserStore } from '@/stores/userStore'

defineProps<{ open: boolean }>()
const emit = defineEmits<{ close: [] }>()

type Tab = 'login' | 'register'

const store = useUserStore()

const tab = ref<Tab>('login')
const loading = ref(false)
const showPassword = ref(false)
const globalError = ref<string | null>(null)
const fieldErrors = ref<Record<string, string>>({})

const form = ref({
  name: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

function fieldError(key: string): string | undefined {
  return fieldErrors.value[key]
}

function switchTab(next: Tab): void {
  tab.value = next
  globalError.value = null
  fieldErrors.value = {}
}

function resetForm(): void {
  form.value = { name: '', email: '', password: '', passwordConfirmation: '' }
  globalError.value = null
  fieldErrors.value = {}
  showPassword.value = false
}

async function submit(): Promise<void> {
  globalError.value = null
  fieldErrors.value = {}
  loading.value = true

  const isLogin = tab.value === 'login'
  const url = isLogin ? '/api/auth/login' : '/api/auth/register'

  const body = isLogin
    ? { email: form.value.email, password: form.value.password }
    : {
        name: form.value.name,
        email: form.value.email,
        password: form.value.password,
        password_confirmation: form.value.passwordConfirmation,
      }

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    })

    const json = await response.json()

    if (!response.ok) {
      if (json.errors) {
        fieldErrors.value = Object.fromEntries(
          Object.entries(json.errors as Record<string, string[]>).map(([k, v]) => [k, v[0]]),
        )
      } else {
        globalError.value = json.message ?? 'Ошибка сервера'
      }
      return
    }

    store.setAuth(json.data.user, json.data.token)
    resetForm()
    emit('close')
  } catch {
    globalError.value = 'Нет соединения с сервером'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.input-field {
  @apply w-full bg-surface border border-border text-gray-200 placeholder-gray-600
         rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand/50
         focus:border-brand/50 transition-all;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
.modal-fade-enter-active .relative,
.modal-fade-leave-active .relative {
  transition: transform 0.2s ease, opacity 0.2s ease;
}
.modal-fade-enter-from .relative,
.modal-fade-leave-to .relative {
  transform: translateY(-12px);
  opacity: 0;
}

.field-slide-enter-active,
.field-slide-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}
.field-slide-enter-from,
.field-slide-leave-to {
  opacity: 0;
  max-height: 0;
}
.field-slide-enter-to,
.field-slide-leave-from {
  opacity: 1;
  max-height: 120px;
}
</style>
