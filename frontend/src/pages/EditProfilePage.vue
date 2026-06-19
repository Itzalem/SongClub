<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card border-0 shadow-2xl rounded-5 overflow-hidden p-5">
        <h2 class="fw-bold mb-1">Edit Profile</h2>
        <p class="text-muted mb-4">Update your account details</p>

        <div v-if="error" class="alert alert-danger rounded-3">{{ error }}</div>

        <form v-if="form" @submit.prevent="save">
          <div class="mb-3">
            <label class="form-label fw-bold">Username</label>
            <input v-model="form.username" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Email</label>
            <input v-model="form.email" type="email" class="form-control" required>
          </div>
          <div class="mb-4">
            <label class="form-label fw-bold">Bio <span class="text-muted fw-normal">(optional)</span></label>
            <textarea v-model="form.bio" class="form-control" rows="3" placeholder="Tell us about your music taste..."></textarea>
          </div>

          <hr class="my-4">
          <p class="fw-bold mb-3">Change password <span class="text-muted fw-normal">(leave blank to keep current)</span></p>

          <div class="mb-3">
            <label class="form-label">Current password</label>
            <input v-model="form.current_password" type="password" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">New password</label>
            <input v-model="form.new_password" type="password" class="form-control">
          </div>

          <div class="d-flex gap-2 mt-4">
            <router-link :to="`/profile/${auth.user.id}`" class="btn btn-light border flex-grow-1">Cancel</router-link>
            <button class="btn btn-sc-primary flex-grow-1">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const auth   = useAuthStore()
const router = useRouter()
const form   = ref(null)
const error  = ref('')

onMounted(async () => {
  const res  = await api.get(`/api/users/${auth.user.id}`)
  form.value = {
    username:         res.data.username,
    email:            res.data.email,
    bio:              res.data.bio || '',
    current_password: '',
    new_password:     '',
  }
})

async function save() {
  error.value   = ''
  success.value = ''
  try {
    const res = await api.put(`/api/users/${auth.user.id}`, form.value)
    auth.user.username = res.data.username
    localStorage.setItem('user', JSON.stringify(auth.user))
    router.push(`/profile/${auth.user.id}`)
  } catch (e) {
    error.value = e.response?.data?.error || 'Failed to save changes.'
  }
}
</script>
