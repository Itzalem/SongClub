<template>
  <div>
    <div class="d-flex justify-content-between align-items-end mb-5">
      <div>
        <h2 class="fw-extrabold fs-1 mb-1">Explore</h2>
        <p class="text-muted">Discover the latest songs added by the community.</p>
      </div>
      <router-link v-if="auth.isLoggedIn" to="/songs/create" class="btn btn-sc-primary">
        + Add song
      </router-link>
    </div>

    <form class="d-flex gap-2 mb-4 flex-wrap" @submit.prevent="load(1)">
      <input v-model="artist" type="text" class="form-control" placeholder="Filter by artist..." style="min-width:160px;flex:1">
      <input v-model="genre"  type="text" class="form-control" placeholder="Filter by genre..."  style="min-width:160px;flex:1">
      <button type="submit" class="btn btn-sc-outline px-4">Search</button>
      <button v-if="artist || genre" type="button" class="btn btn-light border" @click="artist = ''; genre = ''; load(1)">✕</button>
    </form>

    <div v-if="loading" class="text-center py-5 opacity-50">
      <p class="fs-4">Loading...</p>
    </div>

    <template v-else>
      <div v-if="songs.length === 0" class="text-center py-5 opacity-25">
        <span class="display-1">🌑</span>
        <p class="fs-4 mt-3">No songs here.</p>
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
const genre      = ref('')
const page       = ref(1)
const totalPages = ref(1)
const loading    = ref(true)

async function load(p = 1) {
  loading.value = true
  const params = new URLSearchParams({ page: p, limit: 9 })
  if (artist.value.trim()) params.set('artist', artist.value.trim())
  if (genre.value.trim())  params.set('genre',  genre.value.trim())
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
