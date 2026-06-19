import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/login',        component: () => import('../pages/LoginPage.vue') },
  { path: '/register',     component: () => import('../pages/RegisterPage.vue') },
  { path: '/',             component: () => import('../pages/FeedPage.vue'),       meta: { requiresAuth: true } },
  { path: '/songs',        component: () => import('../pages/SongsPage.vue'),      meta: { requiresAuth: true } },
  { path: '/songs/create',   component: () => import('../pages/CreateSongPage.vue'), meta: { requiresAuth: true } },
  { path: '/songs/:id/edit', component: () => import('../pages/EditSongPage.vue'),   meta: { requiresAuth: true } },
  { path: '/songs/:id',      component: () => import('../pages/SongDetailPage.vue'), meta: { requiresAuth: true } },
  { path: '/profile/:id',      component: () => import('../pages/ProfilePage.vue'),     meta: { requiresAuth: true } },
  { path: '/profile/:id/edit', component: () => import('../pages/EditProfilePage.vue'), meta: { requiresAuth: true } },
  { path: '/admin',        component: () => import('../pages/AdminPage.vue'),       meta: { requiresAuth: true, requiresAdmin: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const { useAuthStore } = await import('../stores/auth')
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) return '/login'
  if (to.meta.requiresAdmin && !auth.isAdmin)   return '/'
})

export default router