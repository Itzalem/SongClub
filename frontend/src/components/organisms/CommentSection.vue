<script setup>
import { ref, onMounted } from 'vue'
import CommentItem from '../molecules/CommentItem.vue'
import api         from '../../utils/axios'
import { useAuthStore } from '../../stores/auth'

const props = defineProps({
  postId: { type: Number, required: true },
})

const auth     = useAuthStore()
const comments = ref([])
const text     = ref('')

async function loadComments() {
  const res  = await api.get(`/api/posts/${props.postId}/comments`)
  comments.value = res.data
}

async function addComment() {
  if (!text.value.trim()) return
  await api.post(`/api/posts/${props.postId}/comments`, { content: text.value })
  text.value = ''
  await loadComments()
}

onMounted(loadComments)
</script>

<template>
  <div class="post-card-comments">
    <CommentItem
      v-for="c in comments"
      :key="c.id"
      :comment="c"
    />
    <p v-if="comments.length === 0" class="text-muted small">No comments yet.</p>

    <div v-if="auth.isLoggedIn" class="d-flex gap-2 mt-3">
      <input
        v-model="text"
        class="form-control form-control-sm"
        placeholder="Add a comment..."
        @keyup.enter="addComment"
      />
      <button class="btn btn-sc-primary btn-sm" @click="addComment">Send</button>
    </div>
  </div>
</template>

