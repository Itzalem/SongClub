<template>
  <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center" to="/">
        <span class="fs-3 me-2">🎧</span>
        <span class="fw-bold">SongClub</span>
      </router-link>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <router-link class="nav-link px-3" to="/songs">Explore</router-link>
          </li>
          <li v-if="auth.isLoggedIn" class="nav-item">
            <router-link class="nav-link px-3" :to="`/profile/${auth.user.id}`">My Profile</router-link>
          </li>
          <li v-if="auth.isAdmin" class="nav-item">
            <router-link class="nav-link px-3" to="/admin">Admin</router-link>
          </li>
        </ul>

        <div class="d-flex align-items-center gap-3">
          <template v-if="auth.isLoggedIn">
            <div class="dropdown">
              <a class="nav-link dropdown-toggle fw-bold text-white" href="#" data-bs-toggle="dropdown">
                {{ auth.user.username }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-3">
                <li>
                  <router-link class="dropdown-item py-2" :to="`/profile/${auth.user.id}`">
                    Profile
                  </router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="px-2">
                  <button class="btn btn-danger btn-sm w-100 rounded-2" @click="logout">Log out</button>
                </li>
              </ul>
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="btn btn-outline-light btn-sm px-4 rounded-pill">Log in</router-link>
            <router-link to="/register" class="btn btn-sc-primary btn-sm px-4 rounded-pill">Join</router-link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

function logout() {
  auth.logout()
  router.push('/login')
}
</script>