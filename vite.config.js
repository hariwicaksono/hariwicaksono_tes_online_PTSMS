import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vuetify from 'vite-plugin-vuetify'
import laravel from 'laravel-vite-plugin'
import { fileURLToPath } from 'url'
import { dirname, resolve } from 'path'
import { VitePWA } from 'vite-plugin-pwa';

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue(),
    vuetify(),
    // Tambahkan plugin PWA
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'robots.txt', 'apple-touch-icon.png'],
      manifest: {
        name: 'AdminkuLaravel App',
        short_name: 'Aplikasi',
        description: 'AdminkuLaravel App',
        theme_color: '#ffffff',
        background_color: '#ffffff',
        start_url: '/',
        display: 'standalone',
        orientation: 'portrait',
        icons: [
          {
            src: '/pwa/android-chrome-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/pwa/android-chrome-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ],
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,png,svg,ico}'],
      }
    })
  ],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'resources/js'),
    },
  },
  server: {
    host: 'localhost',
    port: 5173,
    proxy: {
      '/api': 'http://localhost:8000',
    },
  },
  build: {
    manifest: true,
    outDir: 'public/build',
    emptyOutDir: true,
  },
})
