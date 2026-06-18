<script setup>
defineProps({
  label: { type: String, required: true },
})
</script>

<template>
  <span class="badge-genre">{{ label }}</span>
</template>