<template>
  <div v-if="loading" class="text-center py-5 opacity-50">
    <p class="fs-4">Loading...</p>
  </div>

  <div v-else-if="notFound" class="text-center py-5">
    <p class="fs-4 text-muted">Song not found.</p>
    <router-link to="/songs" class="btn btn-sc-outline mt-3">← Back to catalogue</router-link>
  </div>

  <div v-else class="container">
    <router-link to="/songs" class="back-link mb-4 d-inline-block">← Back to catalogue</router-link>

    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card border-0 shadow-2xl rounded-5 overflow-hidden">
          <div class="card-body p-5">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <span class="badge-genre">{{ song.genre || 'Música' }}</span>
              <a v-if="song.link" :href="song.link" target="_blank" class="btn btn-sc-outline btn-sm">
                Open in Spotify/YT
              </a>
            </div>

            <h1 class="display-4 fw-extrabold text-olive mb-2">{{ song.title }}</h1>
            <p class="fs-3 text-muted mb-4">{{ song.artist }}</p>

            <div v-if="song.album" class="d-flex align-items-center mb-4 text-muted">
              <span class="me-2">💿</span>
              <span class="fw-medium">Album: {{ song.album }}</span>
            </div>

            <div class="p-4 bg-sc-olive-soft rounded-4 mb-5 border-start border-4 border-accent">
              <p class="mb-0 small text-muted">
                Added by <strong>{{ song.creator_name || 'System' }}</strong>
              </p>
            </div>

            <div v-if="auth.isLoggedIn" class="d-flex gap-3">
              <button class="btn btn-like btn-lg flex-grow-1" :class="{ active: isLiked }" @click="toggleLike">
                ❤️ Like

                <span class="like-count badge bg-white text-danger ms-2">{{ likeCount }}</span>
              </button>
              <button class="btn btn-fav btn-lg flex-grow-1" :class="{ active: isFav }" @click="toggleFav">
                ★ Favorite
              </button>
            </div>

            <div v-if="auth.isLoggedIn && (song.created_by === auth.user.id || auth.isAdmin)"
                 class="mt-5 pt-4 border-top d-flex gap-2">
              <router-link :to="`/songs/${song.id}/edit`" class="btn btn-light border btn-sm px-4">
                Edit
              </router-link>
              <button class="btn btn-danger btn-sm px-4 opacity-75" @click="deleteSong">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const route  = useRoute()
const router = useRouter()
const auth   = useAuthStore()

const song      = ref(null)
const isLiked   = ref(false)
const isFav     = ref(false)
const likeCount = ref(0)
const loading   = ref(true)
const notFound  = ref(false)

async function load() {
  try {
    const res       = await api.get(`/api/songs/${route.params.id}`)
    song.value      = res.data
    isLiked.value   = res.data.is_liked   || false
    isFav.value     = res.data.is_fav     || false
    likeCount.value = res.data.like_count || 0
  } catch (e) {
    notFound.value = true
  } finally {
    loading.value = false
  }
}

async function toggleLike() {
  const res = await api.post(`/api/songs/${song.value.id}/like`)
  isLiked.value   = res.data.liked
  likeCount.value = res.data.count
}

async function toggleFav() {
  const res = await api.post(`/api/songs/${song.value.id}/favorite`)
  isFav.value = res.data.favorited
}

async function deleteSong() {
  if (!confirm('Are you sure you want to delete this song?')) return
  await api.delete(`/api/songs/${song.value.id}`)
  router.push('/songs')
}

onMounted(load)
</script>