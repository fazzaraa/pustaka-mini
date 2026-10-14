<script setup>
import { useRouter, useRoute } from 'vue-router'
import api from './api'

const router = useRouter()
const route = useRoute()

async function logout() {
  try { await api.post('/logout') } catch { /* token mungkin sudah tidak valid */ }
  localStorage.removeItem('token')
  router.push('/login')
}
</script>

<template>
  <header class="topbar">
    <h1>Pustaka Mini</h1>
    <button v-if="route.path !== '/login'" class="ghost" @click="logout">Keluar</button>
  </header>
  <main class="container">
    <router-view />
  </main>
</template>