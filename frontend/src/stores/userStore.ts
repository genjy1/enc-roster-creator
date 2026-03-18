import { computed, ref } from 'vue'
import { defineStore } from 'pinia'

interface AuthUser {
  id: number
  name: string
  email: string
}

const TOKEN_KEY = 'enc_token'

export const useUserStore = defineStore('user', () => {
  const token = ref<string | null>(localStorage.getItem(TOKEN_KEY))
  const user = ref<AuthUser | null>(null)

  const isAuth = computed(() => token.value !== null)

  function setAuth(authUser: AuthUser, authToken: string): void {
    user.value = authUser
    token.value = authToken
    localStorage.setItem(TOKEN_KEY, authToken)
  }

  function clearAuth(): void {
    user.value = null
    token.value = null
    localStorage.removeItem(TOKEN_KEY)
  }

  async function logout(): Promise<void> {
    if (!token.value) return

    await fetch('/api/auth/logout', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token.value}`,
      },
    }).catch(() => null)

    clearAuth()
  }

  return { token, user, isAuth, setAuth, clearAuth, logout }
})
