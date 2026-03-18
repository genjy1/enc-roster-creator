<script setup lang="ts">
import NavBar from './components/NavBar.vue'
</script>

<template>
  <div class="flex flex-col min-h-screen bg-surface">
    <NavBar />
    <main class="flex-1">
      <RouterView />
    </main>
  </div>
</template>
