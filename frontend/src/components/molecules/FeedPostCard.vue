<script setup>
import { ref } from 'vue'
import CommentSection  from '../organisms/CommentSection.vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../utils/axios'

const props = defineProps({
  post: { type: Object, required: true },
})

const auth         = useAuthStore()
const showComments = ref(false)
const isLiked      = ref(props.post.is_liked   ?? false)
const isFav        = ref(props.post.is_favorited ?? false)
const likeCount    = ref(props.post.like_count  ?? 0)

function formatDate(dt) {
  return new Date(dt).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function toggleLike() {
  if (!auth.isLoggedIn) return
  const res  = await api.post(`/api/songs/${props.post.song_id}/like`)
  isLiked.value   = res.data.liked
  likeCount.value = res.data.count
}

async function toggleFav() {
  if (!auth.isLoggedIn) return
  const res = await api.post(`/api/songs/${props.post.song_id}/favorite`)
  isFav.value = res.data.favorited
}
</script>

<template>
  <article class="post-card mb-5 border-0">
    <div class="p-4 p-md-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="badge-genre">{{ post.song_genre || 'Música' }}</span>
        <small class="text-muted fw-medium">{{ formatDate(post.created_at) }}</small>
      </div>

      <div class="mb-2">
        <small class="text-muted">
          🎶 Listening to —
          <router-link :to="`/profile/${post.user_id}`" class="text-decoration-none fw-bold" style="color:var(--sc-olive)">
            @{{ post.username }}
          </router-link>
        </small>
      </div>

      <h2 class="fw-bold mb-1 text-olive">{{ post.song_title }}</h2>
      <p class="text-muted fs-4 mb-4">{{ post.song_artist }}</p>

      <div v-if="post.caption" class="post-caption">"{{ post.caption }}"</div>

      <div class="d-flex gap-2 mt-3 flex-wrap">
        <a v-if="post.song_link" :href="post.song_link" target="_blank" class="btn btn-sc-primary flex-grow-1">
          ▶ Listen now
        </a>

        <button
          v-if="auth.isLoggedIn"
          class="btn btn-like btn-sm"
          :class="{ active: isLiked }"
          @click="toggleLike"
        >
          ♥ {{ likeCount > 0 ? likeCount : '' }}
        </button>

        <button
          v-if="auth.isLoggedIn"
          class="btn btn-fav btn-sm"
          :class="{ active: isFav }"
          @click="toggleFav"
        >
          ★
        </button>

        <button class="btn btn-sc-outline btn-sm" @click="showComments = !showComments">
          💬 {{ post.comment_count > 0 && !showComments ? post.comment_count : '' }}
        </button>
      </div>
    </div>

    <CommentSection v-if="showComments" :post-id="post.id" />
  </article>
</template>