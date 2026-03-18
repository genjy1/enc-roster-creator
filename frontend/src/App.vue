<script setup lang="ts">
import { ref } from 'vue'
import NavBar from './components/NavBar.vue'
import AuthModal from './components/AuthModal.vue'
import AppPreloader from './components/AppPreloader.vue'

const authModalOpen = ref(false)
const preloaderDone = ref(false)
</script>

<template>
  <AppPreloader v-if="!preloaderDone" @done="preloaderDone = true" />

  <Transition name="app-fade">
    <div v-if="preloaderDone" class="flex flex-col min-h-screen bg-surface">
      <NavBar @open-auth="authModalOpen = true" />
      <main class="flex-1">
        <RouterView />
      </main>
      <AuthModal :open="authModalOpen" @close="authModalOpen = false" />
    </div>
  </Transition>
</template>

<style>
.app-fade-enter-active {
  transition: opacity 0.4s ease;
}
.app-fade-enter-from {
  opacity: 0;
}
</style>
