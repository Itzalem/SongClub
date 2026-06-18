<script setup>
import SongCard      from '../molecules/SongCard.vue'
import PaginationBar from '../molecules/PaginationBar.vue'

defineProps({
  songs:    { type: Array,  required: true },
  likedIds: { type: Array,  default: () => [] },
  favIds:   { type: Array,  default: () => [] },
  page:     { type: Number, default: 1 },
  totalPages:{ type: Number, default: 1 },
})
defineEmits(['liked', 'favd', 'page-change'])
</script>

<template>
  <div>
    <div class="row g-4">
      <div v-for="song in songs" :key="song.id" class="col-md-6 col-lg-4">
        <SongCard
          :song="song"
          :isLiked="likedIds.includes(song.id)"
          :isFav="favIds.includes(song.id)"
          @liked="$emit('liked', $event)"
          @favd="$emit('favd', $event)"
        />
      </div>
    </div>
    <PaginationBar
      v-if="totalPages > 1"
      :page="page"
      :totalPages="totalPages"
      class="mt-4"
      @change="$emit('page-change', $event)"
    />
  </div>
</template>