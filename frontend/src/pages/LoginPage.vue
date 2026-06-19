<template>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card border-0 shadow-2xl rounded-5 overflow-hidden p-5">
        <h2 class="fw-bold mb-1">Welcome back 🎧</h2>
        <p class="text-muted mb-4">Sign in to your SongClub account</p>

        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input v-model="email" type="email" class="form-control" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Password</label>
            <input v-model="password" type="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-sc-primary w-100">Log in</button>
        </form>

        <p class="text-center mt-4 text-muted">
          Don't have an account?
          <router-link to="/register" class="fw-bold" style="color:var(--sc-olive)">Join</router-link>
        </p>
      </div>
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
