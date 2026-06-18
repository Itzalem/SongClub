<template>
  <NavBar />
  <main class="container mx-auto px-4 py-8">
    <router-view />
  </main>
</template>

<script setup>
import NavBar from './components/organisms/NavBar.vue'
</script>