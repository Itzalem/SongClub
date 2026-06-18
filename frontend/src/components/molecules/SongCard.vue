<template>
  <article class="post-card h-100 d-flex flex-column p-4">
    <div class="mb-3">
      <span class="badge-genre">{{ song.genre || 'Música' }}</span>
    </div>

    <h4 class="fw-bold mb-1">
      <router-link :to="`/songs/${song.id}`" class="text-decoration-none text-dark">
        {{ song.title }}
      </router-link>
    </h4>
    <p class="fs-5 text-muted mb-4">{{ song.artist }}</p>

    <div class="mt-auto d-flex justify-content-between align-items-center">
      <a v-if="song.link" :href="song.link" target="_blank" class="btn btn-sc-outline btn-sm px-4">
        ▶ Escuchar
      </a>
      <div v-if="auth.isLoggedIn" class="btn-group shadow-sm rounded-pill">
        <button class="btn btn-like btn-sm" :class="{ active: isLiked }" @click="toggleLike">♥</button>
        <button class="btn btn-fav  btn-sm" :class="{ active: isFav  }" @click="toggleFav">★</button>
      </div>
    </div>
  </article>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import api from '../../utils/axios'

const props = defineProps({
  song:      { type: Object, required: true },
  isLiked:   { type: Boolean, default: false },
  isFav:     { type: Boolean, default: false },
})

const emit = defineEmits(['liked', 'favd'])

const auth = useAuthStore()

async function toggleLike() {
  if (!auth.isLoggedIn) return
  const res = await api.post(`/api/songs/${props.song.id}/like`)
  emit('liked', { songId: props.song.id, liked: res.data.liked })
}

async function toggleFav() {
  if (!auth.isLoggedIn) return
  const res = await api.post(`/api/songs/${props.song.id}/favorite`)
  emit('favd', { songId: props.song.id, favorited: res.data.favorited })
}
</script>