<script setup>
import { ref, watch, onMounted } from 'vue'
import SongCard      from '../molecules/SongCard.vue'
import PaginationBar from '../molecules/PaginationBar.vue'
import api           from '../../utils/axios'

const props = defineProps({
  userId:  { type: Number,  required: true },
  type:    { type: String,  default: 'favorites' },
  title:   { type: String,  default: 'Favorites' },
  isOwner: { type: Boolean, default: false },
})

const songs      = ref([])
const artist     = ref('')
const page       = ref(1)
const totalPages = ref(1)

const isFavList  = props.type === 'favorites'
const isLikeList = props.type === 'liked'

async function load() {
  const res = await api.get(`/api/users/${props.userId}/${props.type}`, {
    params: { artist: artist.value, page: page.value, limit: 6 }
  })
  songs.value      = res.data.data
  totalPages.value = res.data.meta.total_pages
}

function search() { page.value = 1; load() }

function onLiked({ songId, liked }) {
  if (!liked) songs.value = songs.value.filter(s => s.id !== songId)
}

function onFavd({ songId, favorited }) {
  if (!favorited) songs.value = songs.value.filter(s => s.id !== songId)
}

watch(page, load)
onMounted(load)
</script>

<template>
  <div>
    <h5 class="fw-bold text-olive mb-3">{{ title }}</h5>

    <form class="d-flex gap-2 mb-4" @submit.prevent="search">
      <input v-model="artist" class="form-control" placeholder="Filter by artist...">
      <button type="submit" class="btn btn-sc-primary">Search</button>
    </form>

    <div v-if="songs.length === 0" class="text-muted">No songs here yet.</div>

    <div class="row g-3">
      <div v-for="song in songs" :key="song.id" class="col-md-6">
        <SongCard
          :song="song"
          :removeMode="isOwner ? (isLikeList ? 'like' : 'fav') : null"
          @liked="onLiked"
          @favd="onFavd"
        />
      </div>
    </div>

    <PaginationBar
      v-if="totalPages > 1"
      :page="page"
      :totalPages="totalPages"
      class="mt-4"
      @change="p => { page = p }"
    />
  </div>
</template>
