<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card border-0 shadow-2xl rounded-5 overflow-hidden p-5">
        <h2 class="fw-bold mb-1">Edit song 🎵</h2>
        <p class="text-muted mb-4">Update the details of this song</p>

        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

        <form v-if="form" @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label fw-bold">Title *</label>
            <input v-model="form.title" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Artist *</label>
            <input v-model="form.artist" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Album</label>
            <input v-model="form.album" type="text" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Genre</label>
            <input v-model="form.genre" type="text" class="form-control" placeholder="Pop, Rock, Jazz...">
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Link (Spotify / YouTube)</label>
            <input v-model="form.link" type="url" class="form-control">
          </div>
          <div class="d-flex gap-2">
            <router-link :to="`/songs/${route.params.id}`" class="btn btn-light border flex-grow-1">Cancel</router-link>
            <button class="btn btn-sc-primary flex-grow-1">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../utils/axios'

const route  = useRoute()
const router = useRouter()
const form   = ref(null)
const error  = ref('')

onMounted(async () => {
  const res = await api.get(`/api/songs/${route.params.id}`)
  const s   = res.data
  form.value = { title: s.title, artist: s.artist, album: s.album || '', genre: s.genre || '', link: s.link || '' }
})

async function submit() {
  error.value = ''
  try {
    await api.put(`/api/songs/${route.params.id}`, form.value)
    router.push(`/songs/${route.params.id}`)
  } catch (e) {
    error.value = e.response?.data?.error || 'Failed to save changes.'
  }
}
</script>
