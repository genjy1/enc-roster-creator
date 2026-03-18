<template>
  <header class="sticky top-0 z-50 bg-card border-b border-border">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-14">
      <!-- Logo -->
      <RouterLink to="/" class="text-brand font-black text-xl tracking-widest uppercase">
        ENC
      </RouterLink>

      <!-- Navigation -->
      <nav>
        <ul class="flex items-center gap-6">
          <li v-for="link in links" :key="link.to">
            <RouterLink
              :to="link.to"
              class="text-sm text-gray-400 hover:text-white"
              active-class="text-white font-semibold"
            >
              {{ link.label }}
            </RouterLink>
          </li>
        </ul>
      </nav>

      <!-- CTA -->
      <RouterLink
        to="/roster/add"
        class="flex items-center gap-2 bg-brand hover:bg-brand-hover text-white text-sm font-semibold px-4 py-1.5 rounded transition-colors"
      >
        <span>+</span>
        <span>Roster</span>
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
