<template>
  <div>
    <div class="d-flex justify-content-between align-items-end mb-5">
      <div>
        <h2 class="fw-extrabold fs-1 mb-1">Explorar</h2>
        <p class="text-muted">Descubre las últimas joyas añadidas por la comunidad.</p>
      </div>
      <router-link v-if="auth.isLoggedIn" to="/songs/create" class="btn btn-sc-primary">
        + Añadir canción
      </router-link>
    </div>

    <form class="d-flex gap-2 mb-4" @submit.prevent="load(1)">
      <input v-model="artist" type="text" class="form-control" placeholder="Filtrar por artista...">
      <button type="submit" class="btn btn-sc-outline px-4">Buscar</button>
      <button v-if="artist" type="button" class="btn btn-light border" @click="artist = ''; load(1)">✕</button>
    </form>

    <div v-if="loading" class="text-center py-5 opacity-50">
      <p class="fs-4">Cargando...</p>
    </div>

    <template v-else>
      <div v-if="songs.length === 0" class="text-center py-5 opacity-25">
        <span class="display-1">🌑</span>
        <p class="fs-4 mt-3">No hay canciones aquí.</p>
      </div>
      <SongList
        v-else
        :songs="songs"
        :liked-ids="likedIds"
        :fav-ids="favIds"
        :page="page"
        :total-pages="totalPages"
        @liked="onLiked"
        @favd="onFavd"
        @page-change="load"
      />
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import SongList from '../components/organisms/SongList.vue'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const auth       = useAuthStore()
const songs      = ref([])
const likedIds   = ref([])
const favIds     = ref([])
const artist     = ref('')
const page       = ref(1)
const totalPages = ref(1)
const loading    = ref(true)

async function load(p = 1) {
  loading.value = true
  const params = new URLSearchParams({ page: p, limit: 9 })
  if (artist.value.trim()) params.set('artist', artist.value.trim())
  const res = await api.get(`/api/songs?${params}`)
  songs.value      = res.data.data
  totalPages.value = res.data.meta.total_pages
  page.value       = p
  loading.value    = false
}

async function loadInteractions() {
  if (!auth.isLoggedIn) return
  const [lRes, fRes] = await Promise.all([
    api.get(`/api/users/${auth.user.id}/liked`),
    api.get(`/api/users/${auth.user.id}/favorites`),
  ])
  likedIds.value = lRes.data.data.map(s => s.id)
  favIds.value   = fRes.data.data.map(s => s.id)
}

function onLiked({ songId, liked }) {
  if (liked) likedIds.value.push(songId)
  else likedIds.value = likedIds.value.filter(id => id !== songId)
}

function onFavd({ songId, favorited }) {
  if (favorited) favIds.value.push(songId)
  else favIds.value = favIds.value.filter(id => id !== songId)
}

onMounted(() => { load(); loadInteractions() })
</script>