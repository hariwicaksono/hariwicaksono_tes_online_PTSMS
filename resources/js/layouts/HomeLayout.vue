<template>
    <v-app>
        <!-- ========================= Preloader Start ========================= -->
                <transition name="fade">
                    <div v-if="isLoading" class="preloader">
                        <div class="loader">
                            <div class="loader-logo">
                                <img :src="siteLogo" alt="Preloader" width="64" style="margin-top: 5px" />
                            </div>
                            <div class="spinner">
                                <div class="spinner-container">
                                    <div class="spinner-rotator">
                                        <div class="spinner-left">
                                            <div class="spinner-circle"></div>
                                        </div>
                                        <div class="spinner-right">
                                            <div class="spinner-circle"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
                <!-- ========================= Preloader End ========================= -->

        <!-- App Bar -->
        <v-app-bar app color="primary" dark elevation="2">
            <v-app-bar-title>
                <RouterLink to="/" class="text-decoration-none" :title="siteName" :alt="siteName"
                    style="color: var(--v-theme-primary)">{{ siteName }}</RouterLink>
            </v-app-bar-title>
            <v-spacer />

            <!-- Login Button / User Menu -->
            <v-btn v-if="!userEmail" :variant="smAndDown ? 'text' : 'text'" router to="/login" class="mr-3"
                :icon="smAndDown">
                <!-- Mobile: hanya icon -->
                <v-icon v-if="smAndDown">mdi-login-variant</v-icon>
                <!-- Desktop: icon + text -->
                <template v-else>
                    <v-icon start>mdi-login-variant</v-icon> Login
                </template>
            </v-btn>

            <!-- User Dropdown -->
            <v-menu offset-y location="bottom left" v-else>
                <template #activator="{ props }">
                    <v-btn variant="text" v-bind="props" class="d-flex align-center text-white mr-4">
                        <v-avatar size="32" class="mr-2">
                            <v-img :src="`https://ui-avatars.com/api/?name=${userName}`" />
                        </v-avatar>
                        <span class="mr-1">
                            <span class="d-none d-sm-flex">{{ userEmail }}</span>
                            <span v-if="loading1" class="ml-2">
                                <v-progress-circular indeterminate size="20"></v-progress-circular>
                            </span>
                        </span>
                        <v-icon size="20">mdi-menu-down</v-icon>
                    </v-btn>
                </template>
                <v-card>
                    <v-list>
                        <v-list-item>
                            <v-list-item-title>{{ userEmail }}</v-list-item-title>
                        </v-list-item>
                        <v-divider />
                        <v-list-item link to="/dashboard" prepend-icon="mdi-view-dashboard">
                            <v-list-item-title>{{ $t('dashboard') }}</v-list-item-title>
                        </v-list-item>
                        <v-list-item @click="confirmLogout" prepend-icon="mdi-logout">
                            <v-list-item-title>{{ $t('logout') }}</v-list-item-title>
                        </v-list-item>
                    </v-list>
                </v-card>
            </v-menu>

            <!-- Settings Menu -->
            <v-menu offset-y location="bottom left" :close-on-content-click="false">
                <template #activator="{ props }">
                    <v-btn icon variant="text" v-bind="props" class="mr-5">
                        <v-icon>mdi-cog</v-icon>
                    </v-btn>
                </template>

                <v-card>
                    <!-- Dark Mode Toggle -->
                    <v-list>
                        <v-list-item @click="toggleDark"
                            :prepend-icon="isDark ? 'mdi-weather-night' : 'mdi-white-balance-sunny'">
                            <v-list-item-title>
                                {{ isDark ? 'Dark Mode' : 'Light Mode' }}
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>

                    <v-divider />

                    <!-- Language Select -->
                    <v-card-text>
                        <v-select v-model="locale" :items="languages" item-title="label" item-value="code"
                            @update:modelValue="changeLang" density="compact" variant="outlined" hide-details
                            label="Bahasa" />
                    </v-card-text>
                </v-card>
            </v-menu>
        </v-app-bar>

        <!-- Slot content -->
        <v-main>
            <slot />
        </v-main>

        <!-- Footer -->
        <v-footer class="text-center">
            <v-container>
                <p class="mb-4">
                    <RouterLink to="/about" class="text-decoration-none px-3">
                        {{ $t('about') }}
                    </RouterLink>
                    <RouterLink to="/terms" class="text-decoration-none px-3">
                        Terms & Conditions
                    </RouterLink>
                    <RouterLink to="/privacy" class="text-decoration-none px-3">
                        Privacy Policy
                    </RouterLink>
                </p>
                <span>&copy; {{ new Date().getFullYear() }} {{ siteName }} . All rights reserved</span>
            </v-container>
        </v-footer>

        <!-- Logout Dialog -->
        <v-dialog v-model="dialogLogout" max-width="400">
            <v-card>
                <v-card-title class="text-h5">
                    {{ $t('confirmation') }} {{ $t('logout') }}
                </v-card-title>
                <v-card-text>{{ $t('confirm_logout') }}?</v-card-text>
                <v-card-actions>
                    <v-spacer />
                    <v-btn variant="text" @click="dialogLogout = false">
                        {{ $t('cancel') }}
                    </v-btn>
                    <v-btn color="red" variant="flat" @click="logout" :loading="loading2">
                        <v-icon>mdi-logout</v-icon>
                        {{ $t('logout') }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme, useDisplay } from 'vuetify'
import api from '@/axios'
import { useSnackbar } from '@/stores/snackbar'
import { initLogout, can } from '@/utils/auth'

const snackbar = useSnackbar()
const router = useRouter()
const isLoading = ref(true)
const loading1 = ref(false)
const loading2 = ref(false)
const isDark = ref(false)
const theme = useTheme()
const { smAndDown } = useDisplay()
const { locale, t } = useI18n()

const userName = ref(null)
const userEmail = ref(null)
const siteName = ref(null)
const siteLogo = ref(null)
const dialogLogout = ref(false)

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

// load user from localStorage
const storedUser = JSON.parse(localStorage.getItem('user'))
if (storedUser && typeof storedUser === 'object') {
    userName.value = storedUser.name
    userEmail.value = storedUser.email
}

function confirmLogout() {
    dialogLogout.value = true
}

const logout = async () => {
    loading2.value = true
    try {
        const response = await api.post('/logout')
        snackbar.showSnackbar(response.data.message, 'success')
        loading2.value = false
    } catch (err) {
        console.warn('Logout failed:', err)
    }
    dialogLogout.value = false
    initLogout()
    userName.value = null
    userEmail.value = null
    router.push('/')
}

onMounted(async () => {
    setTimeout(() => {
            isLoading.value = false
        }, 1000)

    const savedTheme = localStorage.getItem('theme')
    const savedLang = localStorage.getItem('lang')
    isDark.value = savedTheme === 'dark'
    theme.global.name.value = savedTheme || 'light'

    if (savedLang) {
        locale.value = savedLang
    }

    // Load setting
    const storedSetting = JSON.parse(localStorage.getItem('setting'))
    if (storedSetting && typeof storedSetting === 'object') {
        siteName.value = storedSetting.site_name
        siteLogo.value = storedSetting.site_logo
    }
})
</script>
