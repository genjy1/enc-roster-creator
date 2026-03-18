<template>
  <!-- Hero -->
  <section class="relative min-h-[88vh] flex flex-col items-center justify-center text-center overflow-hidden px-6">
    <!-- Background layers -->
    <div class="absolute inset-0 grid-pattern opacity-60" />
    <div class="absolute inset-0" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, rgb(244 151 26 / 0.08) 0%, transparent 70%)" />
    <div class="absolute bottom-0 left-0 right-0 h-32" style="background: linear-gradient(to bottom, transparent, #0c0d0f)" />

    <!-- Content -->
    <div class="relative z-10 flex flex-col items-center gap-8 max-w-4xl">
      <!-- Badge -->
      <div class="flex items-center gap-2 border border-brand/30 bg-brand-dim rounded-full px-4 py-1.5 text-sm text-brand font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse" />
        European Nations Cup — CS2
      </div>

      <!-- Headline -->
      <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black tracking-tight leading-none">
        <span class="text-white block">Roster</span>
        <span class="text-gradient block">Creator</span>
      </h1>

      <p class="text-gray-400 text-lg sm:text-xl max-w-2xl leading-relaxed">
        Управляй составами команд ENC — выбирай страны, добавляй лучших игроков и формируй идеальный ростер для матчей.
      </p>

      <!-- CTA -->
      <div class="flex flex-wrap items-center justify-center gap-4">
        <RouterLink to="/roster/add" class="btn-primary text-base px-7 py-3">
          Создать ростер
        </RouterLink>
        <RouterLink to="/players" class="btn-ghost text-base px-7 py-3">
          Смотреть игроков
        </RouterLink>
      </div>
    </div>

    <!-- Floating stat cards -->
    <div class="relative z-10 mt-16 grid grid-cols-3 gap-4 max-w-lg w-full">
      <div
        v-for="stat in stats"
        :key="stat.label"
        class="bg-card border border-border rounded-xl p-4 text-center"
        style="backdrop-filter: blur(8px)"
      >
        <p class="text-2xl font-black text-gradient">{{ stat.value }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ stat.label }}</p>
      </div>
    </div>
  </section>

  <!-- Feature strip -->
  <section class="border-t border-border">
    <div class="max-w-7xl mx-auto px-6 py-16 grid sm:grid-cols-3 gap-8">
      <div v-for="feat in features" :key="feat.title" class="flex flex-col gap-3">
        <div class="w-10 h-10 rounded-lg bg-brand-dim border border-brand/20 flex items-center justify-center text-brand">
          <component :is="feat.icon" class="w-5 h-5" />
        </div>
        <h3 class="font-bold text-white">{{ feat.title }}</h3>
        <p class="text-sm text-gray-500 leading-relaxed">{{ feat.desc }}</p>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { BoltIcon, FunnelIcon, GlobeEuropeAfricaIcon } from '@heroicons/vue/24/outline'

const stats = [
  { value: '24+', label: 'Страны' },
  { value: '300+', label: 'Игроки' },
  { value: '5v5', label: 'Формат' },
]

const features = [
  {
    icon: GlobeEuropeAfricaIcon,
    title: 'Все страны ENC',
    desc: 'База всех европейских стран с флагами и списком профессиональных игроков.',
  },
  {
    icon: FunnelIcon,
    title: 'Умный подбор',
    desc: 'Фильтрация по позициям — AWP, IGL, Rifler, Support, Lurker — для идеального баланса команды.',
  },
  {
    icon: BoltIcon,
    title: 'Мгновенное сохранение',
    desc: 'Сформируй ростер в несколько кликов и сохрани за одно нажатие.',
  },
]
</script>
