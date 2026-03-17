<template>
  <ul>
    <li v-if="loading">Загрузка...</li>
    <li v-else-if="error">{{ error }}</li>
    <template v-else>
      <li v-for="country in countries" :key="country.id">
        {{ country.code }} — {{ country.name }}
      </li>
    </template>
  </ul>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import type { Country } from '@/types/Api'

const countries = ref<Country[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

async function fetchCountries(): Promise<void> {
  loading.value = true
  error.value = null
  try {
    const response = await fetch(`http://${location.hostname}/api/countries`, {headers:{'Accept': 'application/json'}})
    if (!response.ok) throw new Error(`Ошибка сервера: ${response.status}`)
    countries.value = await response.json() as Country[]
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCountries)
</script>
