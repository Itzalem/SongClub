<template>
  <div v-if="!profileUser" class="text-center py-5 opacity-50">
    <p class="fs-4">Loading profile...</p>
  </div>

  <div v-else class="row justify-content-center">

    <div class="col-lg-10 mb-5">
      <div class="bg-white p-4 p-md-5 rounded-5 shadow-sm border d-md-flex align-items-center text-center text-md-start">
        <div class="profile-avatar shadow-lg mb-3 mb-md-0 me-md-5">
          {{ profileUser.username?.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-grow-1">
          <h1 class="fw-bold mb-2">{{ profileUser.username }}</h1>
          <p class="text-muted fs-5 mb-0">{{ profileUser.bio || 'Music lover on SongClub.' }}</p>
          <router-link v-if="isOwner" :to="`/profile/${userId}/edit`" class="btn btn-sc-outline btn-sm mt-3">Edit Profile</router-link>
        </div>
        <div class="d-flex gap-4 justify-content-center mt-4 mt-md-0 ps-md-5">
          <div class="text-center">
            <div class="fw-bold fs-3">{{ posts.length }}</div>
            <small class="text-muted text-uppercase fw-bold" style="font-size:0.7rem">Posts</small>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-10">
      <ul class="nav nav-pills mb-5 gap-2">
        <li class="nav-item">
          <button
            class="btn"
            :class="activeTab === 'feed' ? 'btn-sc-primary' : 'btn-sc-outline'"
            @click="activeTab = 'feed'"
          >🎵 Wall</button>
        </li>
        <li class="nav-item">
          <button
            class="btn"
            :class="activeTab === 'favs' ? 'btn-sc-primary' : 'btn-sc-outline'"
            @click="activeTab = 'favs'"
          >★ Favorites</button>
        </li>
        <li v-if="isOwner" class="nav-item">
          <button
            class="btn"
            :class="activeTab === 'liked' ? 'btn-sc-primary' : 'btn-sc-outline'"
            @click="activeTab = 'liked'"
          >♥ Likes</button>
        </li>
      </ul>
    </div>

    <div v-if="activeTab === 'feed'" class="col-lg-7">

      <div v-if="isOwner" class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
        <div class="card-body p-4" style="background:var(--sc-olive-soft)">
          <h6 class="fw-bold mb-3">What song defines your day today?</h6>
          <form @submit.prevent="submitPost">
            <select v-model="postSongId" class="form-select border-0 shadow-sm mb-3" required>
              <option value="">Select from the list...</option>
              <option v-for="s in allSongs" :key="s.id" :value="s.id">
                {{ s.title }} — {{ s.artist }}
              </option>
            </select>
            <textarea
              v-model="postCaption"
              class="form-control border-0 shadow-sm mb-3"
              rows="2"
              placeholder="Add a caption..."
            ></textarea>
            <button type="submit" class="btn btn-sc-primary w-100">Post to my wall</button>
          </form>
        </div>
      </div>

      <FeedPostCard v-for="post in posts" :key="post.id" :post="post" class="mb-4" />

      <div v-if="posts.length === 0" class="text-center py-5 opacity-25">
        <span class="display-1">🌑</span>
        <p class="fs-4 mt-3">No recent activity on this wall.</p>
      </div>
    </div>

    <div v-if="activeTab === 'favs'" class="col-lg-10">
      <FavoritesSection :user-id="userId" type="favorites" title="Favorites ★" :is-owner="isOwner" />
    </div>

    <div v-if="activeTab === 'liked' && isOwner" class="col-lg-10">
      <FavoritesSection :user-id="userId" type="liked" title="My Likes ♥" :is-owner="isOwner" />
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import FeedPostCard    from '../components/molecules/FeedPostCard.vue'
import FavoritesSection from '../components/organisms/FavoritesSection.vue'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const route   = useRoute()
const auth    = useAuthStore()
const userId  = computed(() => Number(route.params.id))
const isOwner = computed(() => auth.isLoggedIn && auth.user.id === userId.value)

const profileUser = ref(null)
const posts       = ref([])
const allSongs    = ref([])
const activeTab   = ref('feed')

const postSongId  = ref('')
const postCaption = ref('')

async function load() {
  const [userRes, feedRes] = await Promise.all([
    api.get(`/api/users/${userId.value}`),
    api.get(`/api/feed?user_id=${userId.value}&limit=50`),
  ])
  profileUser.value = userRes.data
  posts.value       = feedRes.data.data

  if (isOwner.value) {
    const songsRes = await api.get('/api/songs?limit=200')
    allSongs.value = songsRes.data.data
  }
}

async function submitPost() {
  if (!postSongId.value) return
  await api.post('/api/posts', { song_id: Number(postSongId.value), caption: postCaption.value })
  postSongId.value  = ''
  postCaption.value = ''
  load()
}

onMounted(load)
</script>
