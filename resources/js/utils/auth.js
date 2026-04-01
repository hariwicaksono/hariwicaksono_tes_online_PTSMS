// js/utils/auth.js
import axios from 'axios'
import { reactive } from 'vue'
import { loadDynamicAdminRoutes } from '@/router';

export const authState = reactive({
  user: null,
  token: null,
  permissions: []
})

export function setAuth(data) {
  authState.user = data.user
  authState.token = data.token
  authState.permissions = data.permissions

  localStorage.setItem('token', data.token)
  localStorage.setItem('user', JSON.stringify(data.user))
  localStorage.setItem('permissions', JSON.stringify(data.permissions))

  axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
}

export function initAuth() {
  const token = localStorage.getItem('token')
  const user = localStorage.getItem('user')
  const permissions = localStorage.getItem('permissions')

  if (token && user && permissions) {
    authState.token = token
    authState.user = JSON.parse(user)
    authState.permissions = JSON.parse(permissions)

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

export function initLogout() {
  authState.user = null
  authState.token = null
  authState.permissions = []
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem('permissions')
  localStorage.removeItem('menus')
  delete axios.defaults.headers.common['Authorization']
}

export function can(permission) {
  return authState.permissions.includes(permission)
}

export async function loadMenus() {
  const token = localStorage.getItem('token')
  if (!token) return

  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  const res = await axios.get('/api/menus')
  localStorage.setItem('menus', JSON.stringify(res.data))
  await loadDynamicAdminRoutes(res.data)
}
