<template>
  <v-app>
    <v-app-bar app color="primary" dark elevation="2">
      <v-toolbar-title>{{ siteName }}</v-toolbar-title>
      <v-spacer />

      <v-menu offset-y location="bottom left" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn icon variant="text" v-bind="props" class="d-flex align-center text-white mr-5">
            <v-avatar size="32">
              <v-icon large color="white">mdi-cog</v-icon>
            </v-avatar>
          </v-btn>
        </template>

        <v-card>
          <!-- Dark Mode Toggle -->
          <v-list>
            <v-list-item @click="toggleDark" :prepend-icon="isDark ? 'mdi-weather-night' : 'mdi-white-balance-sunny'">
              <v-list-item-title>
                {{ isDark ? 'Dark Mode' : 'Light Mode' }}
              </v-list-item-title>
            </v-list-item>
          </v-list>

          <v-divider />

          <!-- Language Select -->
          <v-card-text>
            <v-select v-model="locale" :items="languages" item-title="label" item-value="code"
              @update:modelValue="changeLang" density="compact" variant="outlined" hide-details label="Bahasa" />
          </v-card-text>
        </v-card>
      </v-menu>
    </v-app-bar>

    <v-container fluid class="fill-height login-page">
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="4">
          <v-card elevation="12" class="pa-4 rounded-lg">
            <!-- Header -->
            <div class="text-center mb-4">
              <v-avatar size="64" class="mx-auto mb-2">
                <v-img :src="siteLogo" alt="Logo" />
              </v-avatar>
              <h3 class="text-h6 font-weight-bold">{{ $t('welcome') }}</h3>
            </div>

            <!-- Form -->
            <v-form @submit.prevent="handleLogin" ref="formRef" v-model="valid">
              <v-alert v-if="apiError" type="error" class="mb-4">{{ apiError }}</v-alert>

              <v-text-field v-model="email" :label="$t('email')" type="email" prepend-inner-icon="mdi-email"
                :rules="[rules.required, rules.email]" clearable />

              <v-text-field v-model="password" :label="$t('password')" :type="showPassword ? 'text' : 'password'"
                prepend-inner-icon="mdi-lock" :rules="[rules.required]"
                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                @click:append-inner="showPassword = !showPassword" clearable />

              <v-layout justify-space-between>
                <v-checkbox v-model="checkbox1" color="primary" label="Remember me" class="mt-n3"></v-checkbox>
                <v-spacer></v-spacer>
                <RouterLink to="/forgot-password" class="mt-0">
                  Lost Password?
                </RouterLink>
              </v-layout>

              <v-btn size="large" :loading="loading" :disabled="!valid || loading" color="primary" class="mt-n4 mb-5"
                block type="submit">
                {{ $t('login') }}
              </v-btn>

              <RouterLink to="/" class="text-decoration-none">
                <i class="mdi mdi-chevron-left"></i> Back to Home
              </RouterLink>
            </v-form>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
    <v-footer app class="text-center">
      <v-container>
        <span>&copy; {{ new Date().getFullYear() }} {{ siteName }}</span>
      </v-container>
    </v-footer>
  </v-app>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme } from 'vuetify'
import api from '@/axios'
import { setAuth, loadMenus } from '@/utils/auth'
import { useSnackbar } from '@/stores/snackbar'

const snackbar = useSnackbar()
const { t } = useI18n()
const router = useRouter()
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const valid = ref(false)
const apiError = ref('')
const formRef = ref(null)
const isDark = ref(false)
const theme = useTheme()
const { locale } = useI18n()
const siteName = ref(null)
const siteLogo = ref(null)
const checkbox1 = ref(true)

const languages = [
  { label: 'ID', code: 'id' },
  { label: 'EN', code: 'en' }
]

const changeLang = (lang) => {
  locale.value = lang
  localStorage.setItem('lang', lang)
}

const toggleDark = () => {
  isDark.value = !isDark.value
  theme.global.name.value = isDark.value ? 'dark' : 'light'
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
}

const rules = {
  required: v => !!v || 'Wajib diisi',
  email: v => /.+@.+\..+/.test(v) || 'Email tidak valid',
}

onMounted(async () => {
  const savedTheme = localStorage.getItem('theme')
  const savedLang = localStorage.getItem('lang')
  isDark.value = savedTheme === 'dark'
  theme.global.name.value = savedTheme || 'light'

  if (savedLang) {
    locale.value = savedLang
  }

  try {
    const res = await api.get('/settings/app')
    siteName.value = res.data.site_name
    siteLogo.value = res.data.site_logo
  } catch (e) {
    console.error(t('failed_setting'), e)
    snackbar.showSnackbar(e || t('failed_setting'))
  }
})

const handleLogin = async () => {
  apiError.value = ''
  if (!formRef.value.validate()) return

  loading.value = true
  try {
    const res = await api.post('/login', {
      email: email.value,
      password: password.value,
    })
    setAuth(res.data)
    await loadMenus()
    router.push('/')
    snackbar.showSnackbar(t('login_success'), 'success')
  } catch (err) {
    apiError.value = t('login_failed') || err?.response?.data?.error
    snackbar.showSnackbar('Error: ' + err?.response?.data?.error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  /* background: linear-gradient(to bottom right, #1e3c72, #2a5298); */
  min-height: 100vh;
  padding: 16px;
}
</style>
