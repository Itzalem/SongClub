<template>
  <article class="post-card mb-5 border-0">
    <div class="p-4 p-md-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <span class="badge-genre">{{ post.genre || 'Música' }}</span>
        <small class="text-muted fw-medium">{{ formatDate(post.created_at) }}</small>
      </div>

      <div class="mb-2">
        <small class="text-muted">
          🎶 Escuchando —
          <router-link :to="`/profile/${post.user_id}`" class="text-decoration-none fw-bold" style="color:var(--sc-olive)">
            @{{ post.username }}
          </router-link>
        </small>
      </div>

      <h2 class="fw-bold mb-1 text-olive">{{ post.song_title }}</h2>
      <p class="text-muted fs-4 mb-4">{{ post.song_artist }}</p>

      <div v-if="post.caption" class="post-caption">"{{ post.caption }}"</div>

      <div class="d-flex gap-2 mt-3">
        <a v-if="post.song_link" :href="post.song_link" target="_blank" class="btn btn-sc-primary flex-grow-1">
          Escuchar ahora
        </a>
        <button class="btn btn-sc-outline btn-sm" @click="loadComments">
          {{ showComments ? 'Ocultar' : '💬' }} {{ post.comment_count > 0 && !showComments ? post.comment_count : '' }}
        </button>
      </div>
    </div>

    <div v-if="showComments" class="post-card-comments">
      <ul class="list-unstyled mb-4">
        <CommentItem v-for="c in comments" :key="c.id" :comment="c" />
        <li v-if="comments.length === 0" class="text-muted small">Sin comentarios aún.</li>
      </ul>

      <form v-if="auth.isLoggedIn" @submit.prevent="submitComment">
        <div class="input-group bg-white shadow-sm rounded-pill p-1">
          <input v-model="newComment" type="text" class="form-control border-0 px-3"
                 placeholder="Escribe un comentario...">
          <button class="btn btn-sc-primary rounded-pill px-4">Enviar</button>
        </div>
      </form>
    </div>
  </article>
</template>

$ cat /home/user/SongClub/frontend/src/components/molecules/FeedPostCard.vue

<script setup>
import { ref } from 'vue'
import CommentItem from './CommentItem.vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../utils/axios'

const props = defineProps({
  post: { type: Object, required: true },
})

const auth        = ref(useAuthStore())
const comments    = ref(props.post.comments || [])
const showComments= ref(false)
const newComment  = ref('')
const loadingComments = ref(false)

function formatDate(dt) {
  return new Date(dt).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' })
}

async function loadComments() {
  if (showComments.value) { showComments.value = false; return }
  loadingComments.value = true
  const res = await api.get(`/api/posts/${props.post.id}/comments`)
  comments.value = res.data
  showComments.value = true
  loadingComments.value = false
}

async function submitComment() {
  const content = newComment.value.trim()
  if (!content) return
  const res = await api.post(`/api/posts/${props.post.id}/comments`, { content })
  comments.value.push(res.data)
  newComment.value = ''
}
</script>