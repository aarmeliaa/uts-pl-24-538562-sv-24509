import axios from 'axios'
import router from '@/router'
import { clearSession, getToken } from '@/session'

// One Axios instance for the whole app.
// The API address comes from the environment (.env), never from component code.
const http = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    Accept: 'application/json',
  },
})

// Before every request: attach the saved token.
http.interceptors.request.use((config) => {
  const token = getToken()
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// After every response: a 401 means the token is missing or expired,
// so forget it and send the user back to the login page.
// (A 401 from /login itself only means a wrong password.)
http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && error.config.url !== '/login') {
      clearSession()
      router.push({ name: 'login' })
    }
    return Promise.reject(error)
  },
)

export default http
