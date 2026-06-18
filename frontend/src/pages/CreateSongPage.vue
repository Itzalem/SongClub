<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card border-0 shadow-2xl rounded-5 overflow-hidden p-5">
        <h2 class="fw-bold mb-1">Añadir canción 🎵</h2>
        <p class="text-muted mb-4">Comparte una nueva joya con la comunidad</p>

        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label fw-bold">Título *</label>
            <input v-model="form.title" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Artista *</label>
            <input v-model="form.artist" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Álbum</label>
            <input v-model="form.album" type="text" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Género</label>
            <input v-model="form.genre" type="text" class="form-control" placeholder="Pop, Rock, Jazz...">
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Link (Spotify / YouTube)</label>
            <input v-model="form.link" type="url" class="form-control">
          </div>
          <div class="d-flex gap-2">
            <router-link to="/songs" class="btn btn-light border flex-grow-1">Cancelar</router-link>
            <button class="btn btn-sc-primary flex-grow-1">Publicar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../utils/axios'

const router = useRouter()
const form   = ref({ title: '', artist: '', album: '', genre: '', link: '' })
const error  = ref('')

async function submit() {
  error.value = ''
  try {
    const res = await api.post('/api/songs', form.value)
    router.push(`/songs/${res.data.id}`)
  } catch (e) {
    error.value = e.response?.data?.error || 'Error al crear la canción'
  }
}
</script>