import { onUnmounted, ref } from 'vue'

export function useApi() {
  const loading = ref(false)
  const error = ref<string | null>(null)
  let controller: AbortController | null = null

  async function apiFetch<T>(url: string, options?: RequestInit): Promise<T | null> {
    controller?.abort()
    controller = new AbortController()

    loading.value = true
    error.value = null

    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal,
        headers: {
          Accept: 'application/json',
          ...options?.headers,
        },
      })

      if (!response.ok) {
        const json = await response.json().catch(() => null)
        const firstError = json?.errors
          ? (Object.values(json.errors as Record<string, string[]>)[0][0] ?? json.message)
          : (json?.message ?? `Ошибка сервера: ${response.status}`)
        throw new Error(firstError)
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

  return { loading, error, apiFetch }
}
