import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/',            component: () => import('../pages/FeedPage.vue') },
  { path: '/login',       component: () => import('../pages/LoginPage.vue') },
  { path: '/register',    component: () => import('../pages/RegisterPage.vue') },
  { path: '/songs',       component: () => import('../pages/SongsPage.vue') },
  { path: '/songs/create',component: () => import('../pages/CreateSongPage.vue'), meta: { requiresAuth: true } },
  { path: '/songs/:id',   component: () => import('../pages/SongDetailPage.vue') },
  { path: '/profile/:id', component: () => import('../pages/ProfilePage.vue') },
  { path: '/admin',       component: () => import('../pages/AdminPage.vue'), meta: { requiresAuth: true, requiresAdmin: true } },
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