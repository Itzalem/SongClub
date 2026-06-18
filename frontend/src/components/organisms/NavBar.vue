<template>
  <nav class="bg-gray-900 text-white px-6 py-3 flex items-center justify-between">
    <router-link to="/" class="text-xl font-bold text-yellow-400 hover:text-yellow-300">
      SongClub
    </router-link>

    <div class="flex items-center gap-4 text-sm">
      <router-link to="/songs" class="hover:text-yellow-400 transition-colors">Songs</router-link>

      <template v-if="auth.isLoggedIn">
        <router-link :to="`/profile/${auth.user.id}`" class="hover:text-yellow-400 transition-colors">
          {{ auth.user.username }}
        </router-link>
        <router-link v-if="auth.isAdmin" to="/admin" class="hover:text-yellow-400 transition-colors">
          Admin
        </router-link>
        <button @click="handleLogout"
          class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded transition-colors">
          Logout
        </button>
      </template>

      <template v-else>
        <router-link to="/login" class="hover:text-yellow-400 transition-colors">Login</router-link>
        <router-link to="/register"
          class="bg-yellow-400 text-gray-900 font-semibold px-3 py-1 rounded hover:bg-yellow-300 transition-colors">
          Register
        </router-link>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

function handleLogout() {
  auth.logout()
  router.push('/login')
}
</script>