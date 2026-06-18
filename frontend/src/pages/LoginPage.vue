<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <h1 class="text-2xl font-bold mb-6 text-center">Login to SongClub</h1>

      <div v-if="error" class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">{{ error }}</div>

      <form @submit.prevent="handleLogin">
        <div class="mb-4">
          <label for="email" class="block text-sm font-medium mb-1">Email</label>
          <input id="email" v-model="email" type="email" required
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400" />
        </div>
        <div class="mb-6">
          <label for="password" class="block text-sm font-medium mb-1">Password</label>
          <input id="password" v-model="password" type="password" required
            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-400" />
        </div>
        <button type="submit" :disabled="isLoading"
          class="w-full bg-yellow-400 text-gray-900 font-semibold py-2 rounded hover:bg-yellow-300 transition-colors disabled:opacity-50">
          {{ isLoading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="mt-4 text-center text-sm">
        No account?
        <router-link to="/register" class="text-yellow-600 hover:underline">Register</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email     = ref('')
const password  = ref('')
const error     = ref(null)
const isLoading = ref(false)
const auth      = useAuthStore()
const router    = useRouter()

async function handleLogin() {
  error.value     = null
  isLoading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (err) {
    error.value = err.response?.data?.error || 'Login failed.'
  } finally {
    isLoading.value = false
  }
}
</script>