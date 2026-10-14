<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const mode = ref('login') // 'login' | 'register'
const form = reactive({ name: '', email: '', password: '' })
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post(mode.value === 'login' ? '/login' : '/register', form)
    localStorage.setItem('token', data.token)
    router.push('/books')
  } catch (e) {
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : e.response?.data?.message || 'Terjadi kesalahan.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="card narrow">
    <h2>{{ mode === 'login' ? 'Masuk' : 'Daftar' }}</h2>
    <form @submit.prevent="submit">
      <input v-if="mode === 'register'" v-model="form.name" placeholder="Nama" required />
      <input v-model="form.email" type="email" placeholder="Email" required />
      <input v-model="form.password" type="password" placeholder="Password (min. 8 karakter)" required />
      <p v-if="error" class="error">{{ error }}</p>
      <button :disabled="loading">{{ loading ? 'Memproses...' : mode === 'login' ? 'Masuk' : 'Daftar' }}</button>
    </form>
    <p class="switch">
      <a href="#" @click.prevent="mode = mode === 'login' ? 'register' : 'login'">
        {{ mode === 'login' ? 'Belum punya akun? Daftar' : 'Sudah punya akun? Masuk' }}
      </a>
    </p>
  </section>
</template>