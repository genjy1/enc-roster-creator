<template>
  <header class="grid grid-cols-3 px-4 py-2 my-2">
    <div class="logo"><RouterLink to="/">ENC</RouterLink></div>
    <nav>
      <ul class="flex justify-between">
        <li><RouterLink to="/">Главная</RouterLink></li>
        <li><RouterLink to="/players">Игроки</RouterLink></li>
        <li><RouterLink to="/countries">Страны</RouterLink></li>
        <li>
          <RouterLink to="/matches" >Матчи</RouterLink>
        </li>

      </ul>
    </nav>
    <button class="hover:cursor-pointer">
      <RouterLink to="/roster/add" dir="ltr" class="border p-2 rounded-s-lg">+</RouterLink>
      <span dir="rtl" class="border-y border-r p-2 rounded-s-lg">Roster</span>
    </button>
  </header>
</template>
<script lang="ts">


</script>
