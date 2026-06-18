<template>
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="mb-5">
        <h2 class="fw-extrabold fs-1 mb-1">Feed Social</h2>
        <p class="text-muted">Lo que la comunidad está escuchando ahora.</p>
      </div>

      <div v-if="loading" class="text-center py-5 opacity-50">
        <p class="fs-4">Cargando...</p>
      </div>

      <template v-else>
        <div v-if="posts.length === 0" class="text-center py-5 opacity-25">
          <span class="display-1">🌑</span>
          <p class="fs-4 mt-3">El feed está vacío. ¡Sé el primero en publicar!</p>
        </div>
        <FeedList
          v-else
          :posts="posts"
          :page="page"
          :total-pages="totalPages"
          @page-change="load"
        />
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FeedList from '../components/organisms/FeedList.vue'
import api from '../utils/axios'

const posts      = ref([])
const page       = ref(1)
const totalPages = ref(1)
const loading    = ref(true)

async function load(p = 1) {
  loading.value = true
  const res = await api.get(`/api/feed?page=${p}&limit=10`)
  posts.value      = res.data.data
  totalPages.value = res.data.meta.total_pages
  page.value       = p
  loading.value    = false
}

onMounted(() => load())
</script>
