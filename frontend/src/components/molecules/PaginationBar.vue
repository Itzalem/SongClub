<template>
  <div v-if="totalPages > 1" class="d-flex justify-content-center align-items-center gap-3 mt-5">
    <button class="btn btn-sc-outline btn-sm px-4"
            :disabled="page <= 1"
            @click="emit('change', page - 1)">
      ← Anterior
    </button>
    <span class="fw-bold text-muted">{{ page }} / {{ totalPages }}</span>
    <button class="btn btn-sc-outline btn-sm px-4"
            :disabled="page >= totalPages"
            @click="emit('change', page + 1)">
      Siguiente →
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  page:       { type: Number, required: true },
  totalPages: { type: Number, required: true },
})
const emit = defineEmits(['change'])
</script>
