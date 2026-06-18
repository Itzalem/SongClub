<template>
  <div v-if="!profileUser" class="text-center py-5 opacity-50">
    <p class="fs-4">Cargando perfil...</p>
  </div>

  <div v-else class="row justify-content-center">
    <!-- Header del perfil -->
    <div class="col-lg-10 mb-5">
      <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border d-md-flex align-items-center text-center text-md-start">
        <div class="profile-avatar shadow-lg mb-3 mb-md-0 me-md-5">
          {{ profileUser.username?.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-grow-1">
          <div class="d-flex flex-column flex-md-row align-items-center gap-3 mb-3">
            <h1 class="fw-bold mb-0">{{ profileUser.username }}</h1>
          </div>
          <p class="text-muted fs-5 mb-0">{{ profileUser.bio || 'Amante de la música en SongClub.' }}</p>
        </div>
        <div class="d-flex gap-4 justify-content-center mt-4 mt-md-0 ps-md-5">
          <div class="text-center">
            <div class="fw-bold fs-3">{{ favSongs.length }}</div>
            <small class="text-muted text-uppercase fw-bold" style="font-size:0.7rem">Favoritos</small>
          </div>
          <div class="text-center">
            <div class="fw-bold fs-3">{{ posts.length }}</div>
            <small class="text-muted text-uppercase fw-bold" style="font-size:0.7rem">Posts</small>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="col-lg-10">
      <ul class="nav nav-pills mb-5 gap-2">
        <li class="nav-item">
          <button class="btn" :class="activeTab === 'feed' ? 'btn-sc-primary' : 'btn-sc-outline'" @click="activeTab = 'feed'">
            🎵 Muro
          </button>
        </li>
        <li class="nav-item">
          <button class="btn" :class="activeTab === 'favs' ? 'btn-sc-primary' : 'btn-sc-outline'" @click="activeTab = 'favs'">
            ★ Favoritos
          </button>
        </li>
        <li v-if="isOwner" class="nav-item">
          <button class="btn" :class="activeTab === 'liked' ? 'btn-sc-primary' : 'btn-sc-outline'" @click="activeTab = 'liked'">
            ♥ Likes
          </button>
        </li>
      </ul>
    </div>

    <!-- Tab: Feed / Muro -->
    <div v-if="activeTab === 'feed'" class="col-lg-7">
      <!-- Form para publicar (solo owner) -->
      <div v-if="isOwner" class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-body p-4" style="background:var(--sc-olive-soft)">
          <h6 class="fw-bold mb-3">¿Qué canción define tu día hoy?</h6>
          <form @submit.prevent="submitPost">
            <select v-model="postSongId" class="form-select border-0 shadow-sm mb-3" required>
              <option value="">Selecciona de la lista...</option>
              <option v-for="s in allSongs" :key="s.id" :value="s.id">
                {{ s.title }} — {{ s.artist }}
              </option>
            </select>
            <textarea v-model="postCaption" class="form-control border-0 shadow-sm mb-3" rows="2"
                      placeholder="Añade un comentario..."></textarea>
            <button class="btn btn-sc-primary w-100">Publicar en mi muro</button>
          </form>
        </div>
      </div>

      <FeedPostCard v-for="post in posts" :key="post.id" :post="post" />

      <div v-if="posts.length === 0" class="text-center py-5 opacity-25">
        <span class="display-1">🌑</span>
        <p class="fs-4 mt-3">No hay actividad reciente en este muro.</p>
      </div>
    </div>

    <!-- Tab: Favoritos -->
    <div v-if="activeTab === 'favs'" class="col-lg-10">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold mb-1">Favoritos ★</h4>
          <p class="text-muted">Las canciones que más te inspiran.</p>
        </div>
      </div>

      <form class="d-flex gap-2 mb-4" @submit.prevent="loadFavorites(1)">
        <input v-model="favArtist" type="text" class="form-control" placeholder="Filtrar por artista...">
        <button class="btn btn-sc-outline px-4">Buscar</button>
        <button v-if="favArtist" type="button" class="btn btn-light border"
                @click="favArtist = ''; loadFavorites(1)">✕</button>
      </form>

      <div class="song-list">
        <div v-for="song in favSongs" :key="song.id"
             class="song-list-item p-3 mb-2 shadow-sm bg-white rounded-4 border-0 d-flex align-items-center">
          <div class="flex-grow-1">
            <router-link :to="`/songs/${song.id}`" class="text-decoration-none">
              <h6 class="fw-bold mb-0 text-olive">{{ song.title }}</h6>
            </router-link>
            <small class="text-muted">{{ song.artist }}</small>
          </div>
          <div class="d-flex gap-2">
            <a v-if="song.link" :href="song.link" target="_blank" class="btn btn-light btn-sm rounded-circle shadow-sm">▶</a>
            <button v-if="isOwner" class="btn btn-fav active btn-sm rounded-circle shadow-sm"
                    @click="toggleFav(song.id)">★</button>
          </div>
        </div>

        <div v-if="favSongs.length === 0" class="text-center py-5 opacity-25">
          <span class="display-1">🌑</span>
          <p class="fs-4 mt-3">Aún no hay favoritos aquí.</p>
        </div>
      </div>

      <PaginationBar :page="favPage" :total-pages="favTotalPages" @change="loadFavorites" />
    </div>

    <!-- Tab: Likes (solo owner) -->
    <div v-if="activeTab === 'liked' && isOwner" class="col-lg-10">
      <div class="mb-4">
        <h4 class="fw-bold mb-1">Mis Likes ♥</h4>
        <p class="text-muted">Canciones que has marcado con like.</p>
      </div>

      <form class="d-flex gap-2 mb-4" @submit.prevent="loadLiked(1)">
        <input v-model="likedArtist" type="text" class="form-control" placeholder="Filtrar por artista...">
        <button class="btn btn-sc-outline px-4">Buscar</button>
        <button v-if="likedArtist" type="button" class="btn btn-light border"
                @click="likedArtist = ''; loadLiked(1)">✕</button>
      </form>

      <div class="song-list">
        <div v-for="song in likedSongs" :key="song.id"
             class="song-list-item p-3 mb-2 shadow-sm bg-white rounded-4 border-0 d-flex align-items-center">
          <div class="flex-grow-1">
            <router-link :to="`/songs/${song.id}`" class="text-decoration-none">
              <h6 class="fw-bold mb-0 text-olive">{{ song.title }}</h6>
            </router-link>
            <small class="text-muted">{{ song.artist }}</small>
          </div>
          <div class="d-flex gap-2">
            <a v-if="song.link" :href="song.link" target="_blank" class="btn btn-light btn-sm rounded-circle shadow-sm">▶</a>
            <button class="btn btn-like active btn-sm rounded-circle shadow-sm"
                    @click="toggleLiked(song.id)">♥</button>
          </div>
        </div>

        <div v-if="likedSongs.length === 0" class="text-center py-5 opacity-25">
          <span class="display-1">🌑</span>
          <p class="fs-4 mt-3">No has dado like a ninguna canción.</p>
        </div>
      </div>

      <PaginationBar :page="likedPage" :total-pages="likedTotalPages" @change="loadLiked" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FeedPostCard from '../components/molecules/FeedPostCard.vue'
import PaginationBar from '../components/molecules/PaginationBar.vue'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const route   = useRoute()
const auth    = useAuthStore()
const userId  = computed(() => Number(route.params.id))
const isOwner = computed(() => auth.isLoggedIn && auth.user.id === userId.value)

const profileUser = ref(null)
const posts       = ref([])
const allSongs    = ref([])
const favSongs    = ref([])
const likedSongs  = ref([])
const activeTab   = ref('feed')

// post form
const postSongId  = ref('')
const postCaption = ref('')

// favorites filter + pagination
const favArtist     = ref('')
const favPage       = ref(1)
const favTotalPages = ref(1)

// liked filter + pagination (owner only)
const likedArtist     = ref('')
const likedPage       = ref(1)
const likedTotalPages = ref(1)

async function load() {
  const [userRes, postsRes] = await Promise.all([
    api.get(`/api/users/${userId.value}`),
    api.get(`/api/feed?user_id=${userId.value}&limit=50`),
  ])
  profileUser.value = userRes.data

  // get posts from feed filtered by user
  const feedRes = await api.get(`/api/feed?page=1&limit=50`)
  posts.value = feedRes.data.data.filter(p => p.user_id === userId.value)

  if (isOwner.value) {
    const songsRes = await api.get('/api/songs?limit=200')
    allSongs.value = songsRes.data.data
  }

  loadFavorites()
  if (isOwner.value) loadLiked()
}

async function loadFavorites(p = 1) {
  const params = new URLSearchParams({ page: p, limit: 10 })
  if (favArtist.value.trim()) params.set('artist', favArtist.value.trim())
  const res = await api.get(`/api/users/${userId.value}/favorites?${params}`)
  favSongs.value      = res.data.data
  favTotalPages.value = res.data.meta.total_pages
  favPage.value       = p
}

async function loadLiked(p = 1) {
  const params = new URLSearchParams({ page: p, limit: 10 })
  if (likedArtist.value.trim()) params.set('artist', likedArtist.value.trim())
  const res = await api.get(`/api/users/${userId.value}/liked?${params}`)
  likedSongs.value      = res.data.data
  likedTotalPages.value = res.data.meta.total_pages
  likedPage.value       = p
}

async function submitPost() {
  if (!postSongId.value) return
  await api.post('/api/posts', { song_id: Number(postSongId.value), caption: postCaption.value })
  postSongId.value  = ''
  postCaption.value = ''
  load()
}

async function toggleFav(songId) {
  await api.post(`/api/songs/${songId}/favorite`)
  loadFavorites(favPage.value)
}

async function toggleLiked(songId) {
  await api.post(`/api/songs/${songId}/like`)
  loadLiked(likedPage.value)
}

onMounted(load)
</script>