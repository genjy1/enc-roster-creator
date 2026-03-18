<template>
  <Transition name="preloader" @after-leave="$emit('done')">
    <div v-if="visible" class="preloader-root">

      <!-- Noise texture overlay -->
      <div class="preloader-noise" />

      <!-- Radial brand glow -->
      <div class="preloader-glow" />

      <!-- Content -->
      <div class="relative flex flex-col items-center gap-8 select-none">

        <!-- Logo mark -->
        <div class="relative">
          <!-- Rotating ring -->
          <svg class="preloader-ring" viewBox="0 0 80 80" fill="none">
            <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="1.5"
              class="text-border" />
            <circle cx="40" cy="40" r="36" stroke="currentColor" stroke-width="1.5"
              stroke-dasharray="56 170" stroke-linecap="round"
              class="text-brand preloader-arc" />
          </svg>

          <!-- Center badge -->
          <div class="preloader-badge">
            <span class="font-black text-xl tracking-widest text-black leading-none">EC</span>
          </div>
        </div>

        <!-- Wordmark -->
        <div class="flex flex-col items-center gap-1">
          <p class="text-xs font-bold tracking-[0.3em] uppercase text-gray-600">European Nations Cup</p>
          <p class="text-2xl font-black tracking-[0.15em] uppercase text-gradient">
            Roster Creator
          </p>
        </div>

        <!-- Progress bar -->
        <div class="w-48 h-0.5 bg-border rounded-full overflow-hidden">
          <div class="preloader-bar h-full bg-gradient-to-r from-brand to-amber-400 rounded-full" />
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'

const emit = defineEmits<{ done: [] }>()

const visible = ref(true)

onMounted(() => {
  // Минимальное время показа — 1.4s, затем fade-out
  setTimeout(() => {
    visible.value = false
  }, 1400)
})
</script>

<style scoped>
.preloader-root {
  @reference '../main.css';
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-surface);
}

.preloader-noise {
  position: absolute;
  inset: 0;
  opacity: 0.03;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  background-size: 200px;
}

.preloader-glow {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -60%);
  width: 600px;
  height: 400px;
  background: radial-gradient(ellipse at center, rgb(244 151 26 / 0.12) 0%, transparent 70%);
  pointer-events: none;
}

/* SVG ring wrapper */
.preloader-ring {
  width: 80px;
  height: 80px;
  animation: spin 1.4s linear infinite;
}

.preloader-arc {
  transform-origin: center;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Center badge */
.preloader-badge {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preloader-badge > span {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background-color: #f4971a;
  box-shadow: 0 0 24px rgb(244 151 26 / 0.5);
}

/* Progress bar fill animation */
.preloader-bar {
  animation: fill-bar 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

@keyframes fill-bar {
  from { width: 0%; }
  to   { width: 100%; }
}

/* Page transition */
.preloader-enter-active,
.preloader-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.preloader-enter-from,
.preloader-leave-to {
  opacity: 0;
  transform: scale(1.03);
}
</style>
