<template>
  <header
    class="sticky top-0 z-50 backdrop-blur-xl border-b border-border/60"
    style="background: rgb(12 13 15 / 0.85)"
  >
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-14">
      <!-- Logo -->
      <RouterLink to="/" class="flex items-center gap-2 group">
        <div
          class="w-7 h-7 rounded-md bg-brand flex items-center justify-center text-black font-black text-xs leading-none glow-brand-sm transition-all group-hover:scale-110"
        >
          EC
        </div>
        <span class="font-black text-lg tracking-widest uppercase text-gradient">ENC</span>
      </RouterLink>

      <!-- Navigation -->
      <nav>
        <ul class="flex items-center gap-1">
          <li v-for="link in links" :key="link.to">
            <RouterLink
              :to="link.to"
              class="relative text-sm text-gray-500 hover:text-gray-200 px-3 py-1.5 rounded-md transition-colors"
              active-class="text-white bg-white/5"
            >
              {{ link.label }}
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- Right side -->
      <div class="flex items-center gap-2">
        <!-- Authenticated -->
        <template v-if="store.isAuth">
          <RouterLink to="/roster/add" class="btn-primary text-sm py-1.5 px-4">
            <span class="text-base leading-none">+</span>
            <span>Ростер</span>
          </RouterLink>

          <!-- User menu -->
          <div class="relative" ref="userMenuEl">
            <button
              class="flex items-center gap-2 text-sm text-gray-400 hover:text-white px-3 py-1.5 rounded-md hover:bg-white/5 transition-colors"
              @click="userMenuOpen = !userMenuOpen"
            >
              <span
                class="w-7 h-7 rounded-full bg-brand/20 border border-brand/30 flex items-center justify-center text-brand font-bold text-xs"
              >
                {{ store.user?.name?.[0]?.toUpperCase() ?? '?' }}
              </span>
              <span class="hidden sm:block">{{ store.user?.name }}</span>
              <ChevronDownIcon class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': userMenuOpen }" />
            </button>

            <Transition name="dropdown">
              <div
                v-if="userMenuOpen"
                class="absolute right-0 mt-1 w-44 bg-card border border-border rounded-lg shadow-xl overflow-hidden py-1 z-50"
              >
                <p class="px-4 py-2 text-xs text-gray-600 border-b border-border truncate">
                  {{ store.user?.email }}
                </p>
                <button
                  class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-400 hover:text-red-400 hover:bg-red-500/5 transition-colors"
                  @click="handleLogout"
                >
                  <ArrowRightStartOnRectangleIcon class="w-4 h-4" />
                  Выйти
                </button>
              </div>
            </Transition>
          </div>
        </template>

        <!-- Guest -->
        <button
          v-else
          class="flex items-center gap-2 text-sm py-1.5 px-4 bg-brand hover:bg-brand-hover text-black font-semibold rounded-lg transition-colors"
          @click="$emit('openAuth')"
        >
          <span>Войти</span>
          <ArrowLeftEndOnRectangleIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { onClickOutside } from '@vueuse/core'
import {
  ArrowLeftEndOnRectangleIcon,
  ArrowRightStartOnRectangleIcon,
  ChevronDownIcon,
} from '@heroicons/vue/24/outline'
import { useUserStore } from '@/stores/userStore'

defineEmits<{ openAuth: [] }>()

const store = useUserStore()
const router = useRouter()

const userMenuOpen = ref(false)
const userMenuEl = ref<HTMLElement | null>(null)

onClickOutside(userMenuEl, () => { userMenuOpen.value = false })

async function handleLogout(): Promise<void> {
  userMenuOpen.value = false
  await store.logout()
  router.push('/')
}

const links = [
  { to: '/', label: 'Главная' },
  { to: '/players', label: 'Игроки' },
  { to: '/countries', label: 'Страны' },
  { to: '/matches', label: 'Матчи' },
  { to: '/rosters', label: 'Составы' },
]
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
