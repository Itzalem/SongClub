import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '../utils/axios'

export const useAuthStore = defineStore('auth', () => {
  function readStorage(key) {
    const val = localStorage.getItem(key)
    return val && val !== 'undefined' && val !== 'null' ? val : null
  }

  function parseUser() {
    try { return JSON.parse(readStorage('user')) } catch { return null }
  }

  const token = ref(readStorage('token'))
  const user  = ref(parseUser())

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')

  async function login(email, password) {
    const res = await api.post('/api/auth/login', { email, password })
    token.value = res.data.token
    user.value  = res.data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  async function register(username, email, password, bio) {
    const res = await api.post('/api/auth/register', { username, email, password, bio })
    token.value = res.data.token
    user.value  = res.data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
  }

  function logout() {
    token.value = null
    user.value  = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  }

  return { token, user, isLoggedIn, isAdmin, login, register, logout }
})