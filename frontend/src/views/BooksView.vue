<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '../api'

const STATUS = { want: 'Ingin dibaca', reading: 'Sedang dibaca', done: 'Selesai' }

const books = ref([])
const filter = ref('all')
const error = ref('')
const blank = () => ({ title: '', author: '', status: 'want', rating: null, notes: '' })
const form = reactive(blank())
const editingId = ref(null)

const visible = computed(() =>
  filter.value === 'all' ? books.value : books.value.filter((b) => b.status === filter.value),
)

async function load() {
  const { data } = await api.get('/books')
  books.value = data
}

async function save() {
  error.value = ''
  const payload = { ...form, rating: form.rating || null }
  try {
    if (editingId.value) await api.put(`/books/${editingId.value}`, payload)
    else await api.post('/books', payload)
    reset()
    await load()
  } catch (e) {
    const errs = e.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : 'Gagal menyimpan.'
  }
}

function edit(book) {
  editingId.value = book.id
  Object.assign(form, { title: book.title, author: book.author, status: book.status, rating: book.rating, notes: book.notes || '' })
}

function reset() {
  editingId.value = null
  Object.assign(form, blank())
}

async function remove(book) {
  if (!confirm(`Hapus "${book.title}"?`)) return
  await api.delete(`/books/${book.id}`)
  await load()
}

onMounted(load)
</script>

<template>
  <section class="card">
    <h2>{{ editingId ? 'Ubah buku' : 'Tambah buku' }}</h2>
    <form class="grid" @submit.prevent="save">
      <input v-model="form.title" placeholder="Judul" required />
      <input v-model="form.author" placeholder="Penulis" required />
      <select v-model="form.status">
        <option v-for="(label, key) in STATUS" :key="key" :value="key">{{ label }}</option>
      </select>
      <select v-model.number="form.rating">
        <option :value="null">Tanpa rating</option>
        <option v-for="n in 5" :key="n" :value="n">{{ '★'.repeat(n) }}</option>
      </select>
      <textarea v-model="form.notes" placeholder="Catatan (opsional)" rows="2" />
      <p v-if="error" class="error">{{ error }}</p>
      <div class="actions">
        <button>{{ editingId ? 'Simpan' : 'Tambah' }}</button>
        <button v-if="editingId" type="button" class="ghost" @click="reset">Batal</button>
      </div>
    </form>
  </section>

  <nav class="tabs">
    <button :class="{ active: filter === 'all' }" @click="filter = 'all'">Semua ({{ books.length }})</button>
    <button v-for="(label, key) in STATUS" :key="key" :class="{ active: filter === key }" @click="filter = key">
      {{ label }}
    </button>
  </nav>

  <p v-if="!visible.length" class="muted">Belum ada buku di daftar ini.</p>
  <ul class="list">
    <li v-for="b in visible" :key="b.id" class="card book">
      <div>
        <strong>{{ b.title }}</strong>
        <span class="muted"> — {{ b.author }}</span>
        <div>
          <span class="badge" :class="b.status">{{ STATUS[b.status] }}</span>
          <span v-if="b.rating" class="stars">{{ '★'.repeat(b.rating) }}</span>
        </div>
        <p v-if="b.notes" class="muted">{{ b.notes }}</p>
      </div>
      <div class="actions">
        <button class="ghost" @click="edit(b)">Ubah</button>
        <button class="ghost danger" @click="remove(b)">Hapus</button>
      </div>
    </li>
  </ul>
</template>