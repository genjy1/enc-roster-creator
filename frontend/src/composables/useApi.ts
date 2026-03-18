import { onUnmounted, ref } from 'vue'
import { useUserStore } from '@/stores/userStore'

export function useApi() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  const fieldErrors = ref<Record<string, string>>({})
  let controller: AbortController | null = null

  async function apiFetch<T>(url: string, options?: RequestInit): Promise<T | null> {
    controller?.abort()
    controller = new AbortController()

    loading.value = true
    error.value = null
    fieldErrors.value = {}

    const store = useUserStore()
    const authHeader = store.token ? { Authorization: `Bearer ${store.token}` } : {}

    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal,
        headers: {
          Accept: 'application/json',
          ...authHeader,
          ...options?.headers,
        },
      })

      if (!response.ok) {
        const json = await response.json().catch(() => null)
        if (json?.errors) {
          fieldErrors.value = Object.fromEntries(
            Object.entries(json.errors as Record<string, string[]>).map(([k, v]) => [k, v[0] ?? '']),
          )
          throw new Error(json.message ?? `Ошибка валидации`)
        }
        throw new Error(json?.message ?? `Ошибка сервера: ${response.status}`)
      }

      const json = await response.json()
      return (json?.data ?? json) as T
    } catch (e) {
      if (e instanceof DOMException && e.name === 'AbortError') return null
      error.value = e instanceof Error ? e.message : 'Неизвестная ошибка'
      return null
    } finally {
      loading.value = false
    }
  }

  onUnmounted(() => controller?.abort())

  return { loading, error, fieldErrors, apiFetch }
}
