<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="fw-bold mb-0">User Management</h1>
      <div class="d-flex align-items-center gap-3">
        <span class="badge bg-secondary">{{ users.length }} users</span>
        <button class="btn btn-sc-primary btn-sm" @click="openCreate">+ Create User</button>
      </div>
    </div>

    <!-- Create / Edit form panel -->
    <div v-if="formOpen" class="card border-0 shadow-sm rounded-4 mb-4 p-4">
      <h5 class="fw-bold mb-3">{{ formMode === 'create' ? 'Create User' : 'Edit User' }}</h5>
      <div v-if="formError" class="alert alert-danger py-2">{{ formError }}</div>
      <form @submit.prevent="submitForm">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-bold">Username</label>
            <input v-model="form.username" type="text" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">Email</label>
            <input v-model="form.email" type="email" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-bold">
              Password <span v-if="formMode === 'edit'" class="text-muted fw-normal">(leave blank to keep)</span>
            </label>
            <input v-model="form.password" type="password" class="form-control" :required="formMode === 'create'">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Bio</label>
            <input v-model="form.bio" type="text" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-bold">Role</label>
            <select v-model="form.role" class="form-select">
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <button type="submit" class="btn btn-sc-primary" :disabled="formLoading">
            {{ formLoading ? 'Saving...' : (formMode === 'create' ? 'Create' : 'Save Changes') }}
          </button>
          <button type="button" class="btn btn-sc-outline" @click="closeForm">Cancel</button>
        </div>
      </form>
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
              <div v-else class="d-flex gap-2">
                <button class="btn btn-sm btn-sc-outline" @click="openEdit(u)">Edit</button>
                <button class="btn btn-sm btn-danger" @click="deleteUser(u.id)">Delete</button>
              </div>
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

const formOpen    = ref(false)
const formMode    = ref('create')
const formError   = ref(null)
const formLoading = ref(false)
const editingId   = ref(null)

const form = ref({ username: '', email: '', password: '', bio: '', role: 'user' })

async function load() {
  const res   = await api.get('/api/admin/users')
  users.value = res.data
}

function openCreate() {
  formMode.value  = 'create'
  editingId.value = null
  form.value      = { username: '', email: '', password: '', bio: '', role: 'user' }
  formError.value = null
  formOpen.value  = true
}

function openEdit(u) {
  formMode.value  = 'edit'
  editingId.value = u.id
  form.value      = { username: u.username, email: u.email, password: '', bio: u.bio || '', role: u.role }
  formError.value = null
  formOpen.value  = true
}

function closeForm() {
  formOpen.value = false
}

async function submitForm() {
  formError.value   = null
  formLoading.value = true
  try {
    if (formMode.value === 'create') {
      const res = await api.post('/api/admin/users', form.value)
      users.value.push(res.data)
    } else {
      const payload = { ...form.value }
      if (!payload.password) delete payload.password
      const res = await api.put(`/api/admin/users/${editingId.value}`, payload)
      const idx = users.value.findIndex(u => u.id === editingId.value)
      if (idx !== -1) users.value[idx] = res.data
    }
    closeForm()
  } catch (err) {
    formError.value = err.response?.data?.error || 'Something went wrong.'
  } finally {
    formLoading.value = false
  }
}

async function deleteUser(userId) {
  const u = users.value.find(u => u.id === userId)
  if (!confirm(`Delete ${u?.username}?`)) return
  await api.delete(`/api/admin/users/${userId}`)
  users.value = users.value.filter(u => u.id !== userId)
}

onMounted(load)
</script>
