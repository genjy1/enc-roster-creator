<script setup lang="ts">

import NavBar from './components/NavBar.vue';

</script>

<template>
  <NavBar />
  <div class="container mx-auto w-4/5">
    <RouterView />
  </div>
</template>
