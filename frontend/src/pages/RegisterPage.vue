<template>
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card border-0 shadow-2xl rounded-5 overflow-hidden p-5">
        <h2 class="fw-bold mb-1">Únete a SongClub 🎵</h2>
        <p class="text-muted mb-4">Crea tu cuenta y comparte tu música</p>

        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label fw-bold">Nombre de usuario</label>
            <input v-model="username" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input v-model="email" type="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Contraseña</label>
            <input v-model="password" type="password" class="form-control" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Bio <span class="text-muted fw-normal">(opcional)</span></label>
            <textarea v-model="bio" class="form-control" rows="2" placeholder="Cuéntanos sobre tu gusto musical..."></textarea>
          </div>
          <button class="btn btn-sc-primary w-100">Crear cuenta</button>
        </form>

        <p class="text-center mt-4 text-muted">
          ¿Ya tienes cuenta?
          <router-link to="/login" class="fw-bold" style="color:var(--sc-olive)">Entrar</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth     = useAuthStore()
const router   = useRouter()
const username = ref('')
const email    = ref('')
const password = ref('')
const bio      = ref('')
const error    = ref('')

async function submit() {
  error.value = ''
  try {
    await auth.register(username.value, email.value, password.value, bio.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.error || 'Error al registrarse'
  }
}
</script>