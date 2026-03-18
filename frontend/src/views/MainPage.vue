<template>
  <section class="max-w-7xl mx-auto px-6 py-24 flex flex-col items-center text-center gap-6">
    <h1 class="text-5xl font-black tracking-tight">
      <span class="text-white">ENC Roster</span>
      <span class="text-brand"> Creator</span>
    </h1>
    <p class="text-gray-400 text-lg max-w-xl">
      Управляй составами команд European Nations Cup — выбирай страны, добавляй игроков и формируй ростер для матчей.
    </p>
    <div class="flex gap-4">
      <RouterLink
        to="/roster/add"
        class="bg-brand hover:bg-brand-hover text-white font-semibold px-6 py-2.5 rounded transition-colors"
      >
        Создать ростер
      </RouterLink>
      <RouterLink
        to="/countries"
        class="border border-border text-gray-300 hover:text-white hover:border-gray-500 font-semibold px-6 py-2.5 rounded transition-colors"
      >
        Страны
      </RouterLink>
    </div>
  </section>
</template>
