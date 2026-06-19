<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold mb-0">User Management</h1>
      <span class="badge bg-secondary">{{ users.length }} users</span>
    </div>

    <div v-if="users.length === 0" class="text-muted">No users found.</div>

    <div v-else class="table-responsive">
      <table class="table table-hover align-middle bg-white rounded-3 overflow-hidden shadow-sm">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Bio</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td>{{ u.id }}</td>
            <td>
              <router-link :to="`/profile/${u.id}`">{{ u.username }}</router-link>
            </td>
            <td>{{ u.email }}</td>
            <td>
              <span class="badge" :class="u.role === 'admin' ? 'bg-danger' : 'bg-secondary'">
                {{ u.role }}
              </span>
            </td>
            <td class="text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
              {{ u.bio || '' }}
            </td>
            <td>
              <span v-if="u.id === auth.user.id" class="text-muted small">you</span>
              <button v-else class="btn btn-sm btn-danger" @click="deleteUser(u.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../utils/axios'

const auth  = useAuthStore()
const users = ref([])

async function load() {
  const res  = await api.get('/api/admin/users')
  users.value = res.data
}

async function deleteUser(userId) {
  const u = users.value.find(u => u.id === userId)
  if (!confirm(`Delete ${u?.username}?`)) return
  await api.delete(`/api/admin/users/${userId}`)
  users.value = users.value.filter(u => u.id !== userId)
}

onMounted(load)
</script>