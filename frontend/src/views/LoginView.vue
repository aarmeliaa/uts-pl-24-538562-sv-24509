<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/api/http'
import { saveSession } from '@/session'

const router = useRouter()

const email = ref('')
const password = ref('')
const submitting = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

async function login() {
  submitting.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    const { data } = await http.post('/login', {
      email: email.value,
      password: password.value,
    })
    saveSession(data.token, data.user)
    router.push({ name: 'media' })
  } catch (error) {
    const status = error.response?.status

    if (status === 422) {
      fieldErrors.value = error.response.data.errors
    } else if (status === 401) {
      errorMessage.value = error.response.data.message
    } else if (status === 429) {
      errorMessage.value = 'Too many attempts. Wait a minute and try again.'
    } else {
      errorMessage.value = 'Cannot reach the server. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <div class="login-card">
      <p class="brand mb-1">Media Log</p>
      <p class="text-body-secondary mb-4">
        Keep track of what you watch, read, and listen to.
      </p>

      <form class="panel" novalidate @submit.prevent="login">
        <h1 class="h5 mb-3">Log in</h1>

        <div aria-live="polite">
          <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input
            id="email"
            v-model="email"
            type="email"
            class="form-control"
            :class="{ 'is-invalid': fieldErrors.email }"
            autocomplete="username"
            required
          />
          <div v-if="fieldErrors.email" class="invalid-feedback">{{ fieldErrors.email[0] }}</div>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input
            id="password"
            v-model="password"
            type="password"
            class="form-control"
            :class="{ 'is-invalid': fieldErrors.password }"
            autocomplete="current-password"
            required
          />
          <div v-if="fieldErrors.password" class="invalid-feedback">
            {{ fieldErrors.password[0] }}
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
          {{ submitting ? 'Logging in…' : 'Log in' }}
        </button>
      </form>

      <p class="small text-body-secondary mt-3 mb-0">
        Demo account: demo@example.com / password
      </p>
    </div>
  </main>
</template>
