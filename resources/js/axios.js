import axios from 'axios'
import router from '@/router'
import { initLogout } from '@/utils/auth'

const api = axios.create({ baseURL: '/api' })

let isRefreshing = false
let failedQueue = []

function processQueue(error, token = null) {
  failedQueue.forEach(prom => {
    if (error) prom.reject(error)
    else prom.resolve(token)
  })
  failedQueue = []
}

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  res => res,
  async err => {
    const originalRequest = err.config
    const isLogin = originalRequest.url?.includes('/login')
    const isRefresh = originalRequest.url?.includes('/refresh')

    // Jangan refresh di /login atau /refresh
    if (isLogin || isRefresh) {
      return Promise.reject(err)
    }

    // Jangan retry kalau sudah pernah dicoba
    if (originalRequest._retry) {
      return Promise.reject(err)
    }

    // Jika sedang refresh, masuk antrian
    if (isRefreshing) {
      return new Promise((resolve, reject) => {
        failedQueue.push({ resolve, reject })
      })
        .then(token => {
          originalRequest.headers.Authorization = `Bearer ${token}`
          return api(originalRequest)
        })
        .catch(err => Promise.reject(err))
    }

    // Cek jika 401 dan token perlu direfresh
    if (err.response?.status === 401) {
      originalRequest._retry = true
      isRefreshing = true

      try {
        const oldToken = localStorage.getItem('token')
        if (!oldToken) {
          throw new Error('No token found for refresh')
        }

        const refreshApi = axios.create()
        const response = await refreshApi.post('/api/refresh', null, {
          headers: {
            Authorization: `Bearer ${oldToken}`
          }
        })

        const newToken = response?.data?.token
        const isValidJWT = token => typeof token === 'string' && token.split('.').length === 3

        if (!isValidJWT(newToken)) {
          throw new Error('Invalid token received')
        }

        // Simpan token baru dan lanjutkan request
        localStorage.setItem('token', newToken)
        api.defaults.headers.common.Authorization = `Bearer ${newToken}`
        processQueue(null, newToken)

        originalRequest.headers.Authorization = `Bearer ${newToken}`
        return api(originalRequest)
      } catch (refreshError) {
        const msg = refreshError?.response?.data?.message
        if (
          msg === 'The token has been blacklisted' ||
          msg === 'Token has expired and can no longer be refreshed'
        ) {
          console.warn('Token cannot be refreshed: ', msg)
        } else {
          console.error('Refresh failed: ', refreshError)
        }

        processQueue(refreshError, null)
        initLogout()
        router.push('/login')
        return Promise.reject(refreshError)
      } finally {
        isRefreshing = false
      }
    }

    return Promise.reject(err)
  }
)

export default api
