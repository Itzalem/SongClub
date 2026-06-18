<script setup>
import FeedPostCard  from '../molecules/FeedPostCard.vue'
import PaginationBar from '../molecules/PaginationBar.vue'

defineProps({
  posts:     { type: Array,  required: true },
  page:      { type: Number, default: 1 },
  totalPages:{ type: Number, default: 1 },
})
defineEmits(['page-change'])
</script>

<template>
  <div>
    <FeedPostCard
      v-for="post in posts"
      :key="post.id"
      :post="post"
      class="mb-4"
    />
    <PaginationBar
      v-if="totalPages > 1"
      :page="page"
      :totalPages="totalPages"
      @change="$emit('page-change', $event)"
    />
  </div>
</template>