<script setup>
import { ref, watch, onMounted } from 'vue'
import SongCard      from '../molecules/SongCard.vue'
import PaginationBar from '../molecules/PaginationBar.vue'
import api           from '../../utils/axios'

const props = defineProps({
  userId:   { type: Number, required: true },
  type:     { type: String, default: 'favorites' }, // 'favorites' o 'liked'
  title:    { type: String, default: 'Favoritos' },
})

const songs      = ref([])
const artist     = ref('')
const page       = ref(1)
const totalPages = ref(1)

async function load() {
  const res = await api.get(`/api/users/${props.userId}/${props.type}`, {
    params: { artist: artist.value, page: page.value, limit: 6 }
  })
  songs.value      = res.data.data
  totalPages.value = res.data.meta.total_pages
}

function search() { page.value = 1; load() }

watch(page, load)
onMounted(load)
</script>

<template>
  <div>
    <h5 class="fw-bold text-olive mb-3">{{ title }}</h5>

    <form class="d-flex gap-2 mb-4" @submit.prevent="search">
      <input v-model="artist" class="form-control" placeholder="Filtrar por artista...">
      <button type="submit" class="btn btn-sc-primary">Buscar</button>
    </form>

    <div v-if="songs.length === 0" class="text-muted">No hay canciones aquí aún.</div>

    <div class="row g-3">
      <div v-for="song in songs" :key="song.id" class="col-md-6">
        <SongCard :song="song" :isLiked="false" :isFav="false" />
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