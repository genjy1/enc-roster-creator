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

      <!-- CTA -->
      <RouterLink to="/roster/add" class="btn-primary text-sm py-1.5 px-4">
        <span class="text-base leading-none">+</span>
        <span>Ростер</span>
      </RouterLink>
    </div>
  </header>
</template>

<script setup lang="ts">
const links = [
  { to: '/', label: 'Главная' },
  { to: '/players', label: 'Игроки' },
  { to: '/countries', label: 'Страны' },
  { to: '/matches', label: 'Матчи' },
  { to: '/rosters', label: 'Составы' },
]
</script>
