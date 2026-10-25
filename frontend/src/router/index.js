import { createRouter, createWebHistory } from 'vue-router'
import { getToken } from '@/session'
import LoginView from '@/views/LoginView.vue'
import MediaView from '@/views/MediaView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
    { path: '/', name: 'media', component: MediaView, meta: { requiresAuth: true } },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

// Route guard: pages that need a token redirect to login, and the
// login page redirects to the app when the user is already logged in.
router.beforeEach((to) => {
  const loggedIn = Boolean(getToken())

  if (to.meta.requiresAuth && !loggedIn) {
    return { name: 'login' }
  }
  if (to.meta.guestOnly && loggedIn) {
    return { name: 'media' }
  }
})

export default router
