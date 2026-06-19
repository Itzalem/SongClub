<template>
  <li class="comment-item">
    <div class="d-flex justify-content-between mb-2">
      <span class="comment-author">@{{ comment.username }}</span>
      <small class="text-muted" style="font-size:0.7rem">{{ formatTime(comment.created_at) }}</small>
    </div>
    <p class="mb-0 text-secondary">{{ comment.content }}</p>
  </li>
</template>

<script setup>
defineProps({
  comment: { type: Object, required: true },
})

function formatTime(dt) {
  return new Date(dt).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}
</script>