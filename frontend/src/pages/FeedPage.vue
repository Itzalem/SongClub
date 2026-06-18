<template>
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
          <h2 class="fw-extrabold fs-1 mb-1">Feed Social</h2>
          <p class="text-muted">Lo que la comunidad está escuchando ahora.</p>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5 opacity-50">
        <p class="fs-4">Cargando...</p>
      </div>

      <template v-else>
        <FeedPostCard v-for="post in posts" :key="post.id" :post="post" />

        <div v-if="posts.length === 0" class="text-center py-5 opacity-25">
          <span class="display-1">🌑</span>
          <p class="fs-4 mt-3">El feed está vacío. ¡Sé el primero en publicar!</p>
        </div>

        <PaginationBar :page="page" :total-pages="totalPages" @change="load" />
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FeedPostCard from '../components/molecules/FeedPostCard.vue'
import PaginationBar from '../components/molecules/PaginationBar.vue'
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

