<script setup lang="ts">
import { ref } from 'vue'
import NavBar from './components/NavBar.vue'
import AuthModal from './components/AuthModal.vue'

const authModalOpen = ref(false)
</script>

<template>
  <div class="flex flex-col min-h-screen bg-surface">
    <NavBar @open-auth="authModalOpen = true" />
    <main class="flex-1">
      <RouterView />
    </main>
    <AuthModal :open="authModalOpen" @close="authModalOpen = false" />
  </div>
</template>
