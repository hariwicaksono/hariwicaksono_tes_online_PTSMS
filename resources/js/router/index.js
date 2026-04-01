import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import Home from '@/views/Home.vue'
import Login from '@/views/Login.vue'
import Dashboard from '@/views/Dashboard.vue'
import Error404 from '@/views/404.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { title: 'Home'}
  },
  {
    path: '/about',
    name: 'About',
    component: () => import('@/views/About.vue'),
    meta: { title: 'about' }
  },
  {
    path: '/terms',
    name: 'Terms',
    component: () => import('@/views/Terms.vue'),
    meta: { title: 'terms' }
  },
  {
    path: '/privacy',
    name: 'Privacy',
    component: () => import('@/views/Privacy.vue'),
    meta: { title: 'privacy' }
  },
  { path: '/login', name: 'Login', component: Login, meta: { title: 'Login', guest: true } },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/ForgotPassword.vue'),
    meta: { title: 'Forgot Password' }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/ResetPassword.vue'), meta: { title: 'Reset Password' }
  },
  {
    path: '/',
    name: 'Admin',
    component: MainLayout,
    children: [
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { title: 'dashboard', requiresAuth: true }
      },
      {
        path: '/profile',
        name: 'Profile',
        component: import('@/views/Profile.vue'),
        meta: { title: 'my_profile', requiresAuth: true }
      },
      // Dynamic routes akan di-*push* ke sini saat runtime
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: Error404,
    meta: { title: 'Error 404' }
  }
]

// Buat router
const router = createRouter({
  history: createWebHistory(),
  routes
})

// Fungsi helper untuk memuat dynamic menu
export async function loadDynamicAdminRoutes(menuData) {
  const dynamicRoutes = []

  for (const menu of menuData) {
    // Jika ada route langsung
    if (menu.route) {
      const pathOnly = menu.route.startsWith('/') ? menu.route.slice(1) : menu.route

      const filePath = pathOnly
        .split('/')
        .map(s => s.charAt(0).toUpperCase() + s.slice(1))
        .join('/')

      console.log('Loading route:', pathOnly, '->', filePath)

      dynamicRoutes.push({
        path: pathOnly,
        name: pathOnly.replaceAll('/', '-'),
        component: () => import(`@/views/${filePath}.vue`),
        meta: {
          requiresAuth: true,
          title: menu.title,
          permission: menu.permission_key,
          icon: menu.icon
        }
      })
    }

    // Jika punya anak, proses juga dan gabungkan hasilnya
    if (menu.children && menu.children.length > 0) {
      const childRoutes = await loadDynamicAdminRoutes(menu.children)
      dynamicRoutes.push(...childRoutes)
    }
  }

  // Jika sedang dipanggil dari root (bukan rekursif), tambahkan ke route admin
  const adminRoute = router.getRoutes().find(r => r.name === 'Admin')
  if (adminRoute) {
    dynamicRoutes.forEach(route => {
      router.addRoute('Admin', route)
    })
  }

  return dynamicRoutes // <--- WAJIB ada return ini agar bisa digunakan secara rekursif
}

// Middleware auth
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Cek apakah route membutuhkan auth
  if (to.meta.requiresAuth && !token) {
    // belum login → paksa ke login
    next({ name: 'Login' })
  }
  if (to.meta.guest && token) {
    // sudah login → jangan ke login
    next({ name: 'Dashboard' })
  }
  next()
})

export default router
