import axios from 'axios'

const TOKEN_KEY = 'ifod_token'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api',
  headers: { Accept: 'application/json' },
})

export function setAuthToken(token: string | null) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token)
    api.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    localStorage.removeItem(TOKEN_KEY)
    delete api.defaults.headers.common.Authorization
  }
}

export function getStoredToken(): string | null {
  return localStorage.getItem(TOKEN_KEY)
}

// Restore token on first load.
const stored = getStoredToken()
if (stored) {
  api.defaults.headers.common.Authorization = `Bearer ${stored}`
}

// Redirect to login on 401 (expired/invalid token).
api.interceptors.response.use(
  (r) => r,
  (error) => {
    if (error.response?.status === 401 && getStoredToken()) {
      setAuthToken(null)
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  },
)
