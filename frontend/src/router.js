import { createRouter, createWebHistory } from 'vue-router'
import LoginView from './views/LoginView.vue'
import BooksView from './views/BooksView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/books' },
    { path: '/login', component: LoginView },
    { path: '/books', component: BooksView, meta: { requiresAuth: true } },
  ],
})

router.beforeEach((to) => {
  const loggedIn = !!localStorage.getItem('token')
  if (to.meta.requiresAuth && !loggedIn) return '/login'
  if (to.path === '/login' && loggedIn) return '/books'
})

export default router