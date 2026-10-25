<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { clearSession, getUser } from '@/session'

// Labels for the values the API accepts.
const TYPES = {
  film: 'Film',
  series: 'Series',
  youtube: 'YouTube',
  music: 'Music',
  anime: 'Anime',
  comic: 'Comic',
  book: 'Book',
  other: 'Other',
}
const STATUSES = {
  planned: 'Planned',
  in_progress: 'In progress',
  completed: 'Completed',
  dropped: 'Dropped',
}

const router = useRouter()
const user = getUser()

// ---- List (GET /media) ----
const items = ref([])
const loading = ref(true)
const errorMessage = ref('')
const activeType = ref('all')

// Number of entries per type, e.g. { film: 2, anime: 1 }.
const counts = computed(() => {
  const result = {}
  for (const item of items.value) {
    result[item.type] = (result[item.type] || 0) + 1
  }
  return result
})

// Only show filter buttons for types that have entries.
const usedTypes = computed(() => Object.keys(TYPES).filter((type) => counts.value[type]))

const visibleItems = computed(() => {
  if (activeType.value === 'all') return items.value
  return items.value.filter((item) => item.type === activeType.value)
})

// If the selected type becomes empty (after a delete or edit), go back to "All".
watch(counts, () => {
  if (activeType.value !== 'all' && !counts.value[activeType.value]) {
    activeType.value = 'all'
  }
})

async function loadMedia() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await http.get('/media')
    items.value = data
  } catch {
    errorMessage.value = 'Could not load your media. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(loadMedia)

// ---- Form (POST /media and PUT /media/{id}) ----
const dialog = ref(null)
const editingId = ref(null)
const form = ref(emptyForm())
const fieldErrors = ref({})
const saving = ref(false)

function emptyForm() {
  return { title: '', type: 'film', status: 'planned', rating: '', consumed_at: '', notes: '' }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  fieldErrors.value = {}
  dialog.value.showModal()
}

function openEdit(item) {
  editingId.value = item.id
  form.value = {
    title: item.title,
    type: item.type,
    status: item.status,
    rating: item.rating ?? '',
    consumed_at: item.consumed_at ?? '',
    notes: item.notes ?? '',
  }
  fieldErrors.value = {}
  dialog.value.showModal()
}

function closeDialog() {
  dialog.value.close()
}

async function saveMedia() {
  saving.value = true
  fieldErrors.value = {}

  // Empty optional inputs are sent as null, meaning "no value".
  const payload = {
    ...form.value,
    rating: form.value.rating === '' ? null : Number(form.value.rating),
    consumed_at: form.value.consumed_at || null,
    notes: form.value.notes || null,
  }

  try {
    if (editingId.value) {
      const { data } = await http.put(`/media/${editingId.value}`, payload)
      items.value = items.value.map((item) => (item.id === data.id ? data : item))
    } else {
      const { data } = await http.post('/media', payload)
      items.value.unshift(data)
    }
    closeDialog()
  } catch (error) {
    if (error.response?.status === 422) {
      // Laravel sends { errors: { field: ["message"] } }
      fieldErrors.value = error.response.data.errors
    } else {
      fieldErrors.value = { general: ['Could not save. Please try again.'] }
    }
  } finally {
    saving.value = false
  }
}

// ---- Delete (DELETE /media/{id}) ----
async function deleteMedia(item) {
  if (!window.confirm(`Delete "${item.title}"?`)) return

  errorMessage.value = ''
  try {
    await http.delete(`/media/${item.id}`)
    items.value = items.value.filter((other) => other.id !== item.id)
  } catch {
    errorMessage.value = 'Could not delete. Please try again.'
  }
}

// ---- Logout (POST /logout) ----
async function logout() {
  try {
    await http.post('/logout')
  } catch {
    // The token may already be invalid; log out locally anyway.
  }
  clearSession()
  router.push({ name: 'login' })
}

function formatDate(value) {
  return new Date(`${value}T00:00:00`).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}
</script>

<template>
  <header class="app-header">
    <div class="container d-flex align-items-center justify-content-between py-3">
      <span class="brand">Media Log</span>
      <div class="d-flex align-items-center gap-3">
        <span v-if="user" class="small text-body-secondary d-none d-sm-inline">{{ user.name }}</span>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="logout">
          Log out
        </button>
      </div>
    </div>
  </header>

  <main class="container py-4 py-md-5">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
      <div>
        <h1 class="page-title mb-1">Your log</h1>
        <p class="text-body-secondary mb-0">
          {{ items.length }} {{ items.length === 1 ? 'entry' : 'entries' }}
        </p>
      </div>
      <button type="button" class="btn btn-primary" @click="openCreate">Add media</button>
    </div>

    <div aria-live="polite">
      <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    </div>

    <p v-if="loading" class="text-body-secondary">Loading…</p>

    <div v-else-if="!items.length" class="empty-state">
      <p class="fw-semibold mb-1">Nothing logged yet.</p>
      <p class="text-body-secondary mb-3">Add the first film, album, or series you want to remember.</p>
      <button type="button" class="btn btn-primary" @click="openCreate">Add media</button>
    </div>

    <template v-else>
      <div class="filter-bar mb-3" role="group" aria-label="Filter by type">
        <button
          type="button"
          class="filter-chip"
          :class="{ active: activeType === 'all' }"
          :aria-pressed="activeType === 'all'"
          @click="activeType = 'all'"
        >
          All <span class="count">{{ items.length }}</span>
        </button>
        <button
          v-for="type in usedTypes"
          :key="type"
          type="button"
          class="filter-chip"
          :class="{ active: activeType === type }"
          :aria-pressed="activeType === type"
          @click="activeType = type"
        >
          {{ TYPES[type] }} <span class="count">{{ counts[type] }}</span>
        </button>
      </div>

      <ul class="media-list list-unstyled mb-0">
        <li v-for="item in visibleItems" :key="item.id" class="media-row">
          <div class="media-main">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
              <span class="type-tag">{{ TYPES[item.type] }}</span>
              <span class="status" :class="`status-${item.status}`">{{ STATUSES[item.status] }}</span>
            </div>
            <h2 class="media-title">{{ item.title }}</h2>
            <p v-if="item.notes" class="media-notes">{{ item.notes }}</p>
          </div>

          <div class="media-meta">
            <span v-if="item.rating" class="rating">
              {{ item.rating }}<span class="text-body-secondary">/10</span>
            </span>
            <span v-else class="text-body-secondary small">Not rated</span>
            <span class="date">{{ item.consumed_at ? formatDate(item.consumed_at) : 'No date' }}</span>
          </div>

          <div class="media-actions">
            <button
              type="button"
              class="btn btn-link btn-sm"
              :aria-label="`Edit ${item.title}`"
              @click="openEdit(item)"
            >
              Edit
            </button>
            <button
              type="button"
              class="btn btn-link btn-sm text-danger"
              :aria-label="`Delete ${item.title}`"
              @click="deleteMedia(item)"
            >
              Delete
            </button>
          </div>
        </li>
      </ul>
    </template>
  </main>

  <!-- Native <dialog>: Esc closes it and focus stays inside while open. -->
  <dialog ref="dialog" class="media-dialog" aria-labelledby="media-dialog-title">
    <form novalidate @submit.prevent="saveMedia">
      <h2 id="media-dialog-title" class="h5 mb-3">{{ editingId ? 'Edit media' : 'Add media' }}</h2>

      <div aria-live="polite">
        <div v-if="fieldErrors.general" class="alert alert-danger py-2">{{ fieldErrors.general[0] }}</div>
      </div>

      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': fieldErrors.title }"
          required
        />
        <div v-if="fieldErrors.title" class="invalid-feedback">{{ fieldErrors.title[0] }}</div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label for="type" class="form-label">Type</label>
          <select id="type" v-model="form.type" class="form-select" :class="{ 'is-invalid': fieldErrors.type }">
            <option v-for="(label, value) in TYPES" :key="value" :value="value">{{ label }}</option>
          </select>
          <div v-if="fieldErrors.type" class="invalid-feedback">{{ fieldErrors.type[0] }}</div>
        </div>
        <div class="col-sm-6">
          <label for="status" class="form-label">Status</label>
          <select
            id="status"
            v-model="form.status"
            class="form-select"
            :class="{ 'is-invalid': fieldErrors.status }"
          >
            <option v-for="(label, value) in STATUSES" :key="value" :value="value">{{ label }}</option>
          </select>
          <div v-if="fieldErrors.status" class="invalid-feedback">{{ fieldErrors.status[0] }}</div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label for="rating" class="form-label">Rating <span class="text-body-secondary">(1–10, optional)</span></label>
          <input
            id="rating"
            v-model="form.rating"
            type="number"
            min="1"
            max="10"
            class="form-control"
            :class="{ 'is-invalid': fieldErrors.rating }"
          />
          <div v-if="fieldErrors.rating" class="invalid-feedback">{{ fieldErrors.rating[0] }}</div>
        </div>
        <div class="col-sm-6">
          <label for="consumed_at" class="form-label">Date <span class="text-body-secondary">(optional)</span></label>
          <input
            id="consumed_at"
            v-model="form.consumed_at"
            type="date"
            class="form-control"
            :class="{ 'is-invalid': fieldErrors.consumed_at }"
          />
          <div v-if="fieldErrors.consumed_at" class="invalid-feedback">{{ fieldErrors.consumed_at[0] }}</div>
        </div>
      </div>

      <div class="mb-4">
        <label for="notes" class="form-label">Notes <span class="text-body-secondary">(optional)</span></label>
        <textarea
          id="notes"
          v-model="form.notes"
          rows="3"
          class="form-control"
          :class="{ 'is-invalid': fieldErrors.notes }"
        ></textarea>
        <div v-if="fieldErrors.notes" class="invalid-feedback">{{ fieldErrors.notes[0] }}</div>
      </div>

      <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-outline-secondary" @click="closeDialog">Cancel</button>
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Saving…' : 'Save' }}
        </button>
      </div>
    </form>
  </dialog>
</template>
