<template>
    <v-container fluid>
        <h1 class="mb-3">{{ $t('my_profile') }}</h1>
        <v-card class="mb-6">
            <v-card-title>
                <v-icon icon="mdi-account-circle" size="32" class="me-2" />
                {{ $t('my_profile') }}
            </v-card-title>
            <v-divider />
            <v-card-text v-if="user">
                <v-row>
                    <v-col cols="12" sm="6" class="d-flex align-center">
                        <v-avatar size="64" class="me-4">
                            <v-icon size="64" icon="mdi-account-circle" />
                        </v-avatar>
                        <div>
                            <div class="text-h6">{{ user.name }}</div>
                            <div class="text-caption text-grey">{{ user.email }}</div>
                        </div>
                    </v-col>
                    <v-col cols="12" sm="6">
                        <div class="mb-2">
                            <strong>Role: </strong>
                            <v-chip v-for="role in allRoles" :key="role" color="primary" size="small" class="me-1"
                                variant="flat">
                                <v-icon start icon="mdi-shield-account" />
                                {{ role }}
                            </v-chip>
                        </div>
                        <div>
                            <strong>Status: </strong>
                            <v-chip :color="user.status == 1 ? 'success' : 'error'" size="small" variant="flat">
                                <v-icon start :icon="user.status == 1 ? 'mdi-check-circle' : 'mdi-close-circle'" />
                                {{ user.status == 1 ? 'Active' : 'Inactive' }}
                            </v-chip>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card>
            <v-card-title>
                <v-icon icon="mdi-account-edit" class="me-2" />
                {{ $t('update_profile') }}
                <v-btn variant="text" icon @click="loadProfile">
                    <v-icon color="primary">mdi-refresh</v-icon>
                </v-btn>
            </v-card-title>
            <v-divider />
            <v-card-text>
                <v-form @submit.prevent="updateProfile" ref="form">
                    <v-row>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.name" label="Name" :loading="loading1"
                                prepend-inner-icon="mdi-account" required />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.email" label="Email" type="email" :loading="loading1"
                                prepend-inner-icon="mdi-email" required />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.password" label="New Password"
                                :type="showPassword ? 'text' : 'password'"
                                :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                @click:append-inner="showPassword = !showPassword" prepend-inner-icon="mdi-lock" />
                        </v-col>
                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.password_confirmation" label="Confirm Password"
                                :type="showPassword1 ? 'text' : 'password'"
                                :append-inner-icon="showPassword1 ? 'mdi-eye-off' : 'mdi-eye'"
                                @click:append-inner="showPassword1 = !showPassword1" :rules="[rules.notSameAsPassword]"
                                prepend-inner-icon="mdi-lock-check" />
                        </v-col>
                    </v-row>
                    <v-btn color="primary" class="mt-4" type="submit" :loading="loading"
                        :disabled="form.name === '' || form.email === ''">
                        <v-icon start icon="mdi-content-save" />
                        Update
                    </v-btn>
                </v-form>
            </v-card-text>
        </v-card>
    </v-container>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue'
//import { useRouter } from 'vue-router'
import api from '@/axios'
import router from '@/router'
import { useSnackbar } from '@/stores/snackbar'
import { initLogout } from '../utils/auth'
import { useI18n } from 'vue-i18n'

//const router = useRouter()
const { t } = useI18n()
const user = ref(null)
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})
const loading = ref(false)
const loading1 = ref(false)
const snackbar = useSnackbar()
const allRoles = ref([])
const showPassword = ref(false)
const showPassword1 = ref(false)

const loadProfile = async () => {
    loading1.value = true
    try {
        const res = await api.get('/profile')
        user.value = res.data.user
        form.value = res.data.user
        allRoles.value = res.data.roles
    } catch (err) {
        snackbar.showSnackbar(t('failed_to_load_profile'))
    }
    loading1.value = false
}

onMounted(async () => {
    loadProfile()
})

const updateProfile = async () => {
    loading.value = true
    try {
        const res = await api.put('/profile', form.value)
        snackbar.showSnackbar(res.data.message)

        form.value.password = ''
        form.value.password_confirmation = ''

        if (res.data.relogin) {
            snackbar.showSnackbar(t('relogin_email'))
            setTimeout(() => {
                initLogout()
                //window.location.href = '/login'
                setTimeout(() => {
                    router.replace('/login')
                }, 100)
            }, 1500)
        }
    } catch (err) {
        let msg = err.response?.data?.message || t('failed_to_update_profile')
        snackbar.showSnackbar(msg)
    } finally {
        loading.value = false
        //loadProfile()
    }
}

const rules = {
    notSameAsPassword(value) {
        if (!form.value.password && !value) return true
        if (form.value.password && !value) return t('confirm_password_required')
        if (!form.value.password && value) return t('new_password_required')
        return value === form.value.password || t('confirm_password_mismatch')
    }
}
</script>
