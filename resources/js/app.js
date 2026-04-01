import './bootstrap';
import { ref, createApp, watch } from 'vue';
import App from './App.vue';
import router, { loadDynamicAdminRoutes } from './router';
import vuetify from './plugins/vuetify';
import '@mdi/font/css/materialdesignicons.css';
import '../css/app.css';
import { i18n } from './i18n';
import { initAuth } from './utils/auth';
import helpers from './utils/format';
import eventBus from './eventBus';
import axios from 'axios'
import { registerSW } from 'virtual:pwa-register';

const app = createApp(App);

// Gunakan ref agar bisa direaktifkan
const appName = ref('My App');

registerSW({
    immediate: true,
});

// Tambahkan ke globalProperties
app.config.globalProperties.$appName = appName;
app.config.globalProperties.$eventBus = eventBus;

// Fetch setting dari API
fetch('/api/settings/app')
  .then(res => res.json())
  .then(async (data) => {
    localStorage.setItem('setting', JSON.stringify(data))
    if (data.site_name) appName.value = data.site_name;

    // ✅ Ambil data menu dari API
    const savedMenus = localStorage.getItem('menus')
    const token = localStorage.getItem('token')

    if (token && !savedMenus) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      try {
        const response = await axios.get('/api/menus')
        const menus = response.data
        if (Array.isArray(menus)) {
          localStorage.setItem('menus', JSON.stringify(menus))
          await loadDynamicAdminRoutes(menus)
        } else {
          console.warn('API menus response is not an array', menus)
        }
      } catch (err) {
        console.warn('Failed to load dynamic menu: ', err)
      }
    } else {
      let parsedMenus = []
      try {
        parsedMenus = JSON.parse(savedMenus)
        if (!Array.isArray(parsedMenus)) parsedMenus = []
      } catch (e) {
        console.warn('Failed to parse savedMenus from localStorage', e)
        parsedMenus = []
      }
      await loadDynamicAdminRoutes(parsedMenus)
    }

    // Atur router title dinamis
    router.beforeEach((to, from, next) => {
      const key = to.meta.title || 'untitled';
      const translatedTitle = i18n.global.t(key);
      document.title = `${translatedTitle} - ${appName.value}`;
      next();
    });

    app.use(router).use(vuetify).use(i18n).use(initAuth).use(helpers).mount('#app');
  });

// Tangkap perubahan dari komponen lain
eventBus.on('settings-updated', (data) => {
  if (data.site_name) {
    appName.value = data.site_name;

    const route = router.currentRoute.value;
    const titleKey = route.meta.title || 'untitled';
    const translated = i18n.global.t(titleKey);
    document.title = `${translated} - ${appName.value}`;
  }
});

// Update title saat bahasa berubah
watch(() => i18n.global.locale.value, () => {
  const route = router.currentRoute.value;
  const titleKey = route.meta.title || 'untitled';
  const translated = i18n.global.t(titleKey);
  document.title = `${translated} - ${appName.value}`;
});
