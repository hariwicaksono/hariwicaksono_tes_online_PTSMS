<template>
    <v-container fluid class="fill-height login-page">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="4">
                <v-card max-width="500" elevation="8" class="pa-6">
                    <v-card-title class="text-h5 text-center font-weight-bold mb-4">Reset Password</v-card-title>

                    <v-alert v-if="message" type="success" class="mb-4">
                        {{ message }}
                    </v-alert>

                    <v-form @submit.prevent="submit">
                        <v-text-field v-model="email" label="Email" type="email" :error-messages="errors.email" outlined
                            dense clearable required />
                        <v-text-field v-model="password" label="Password Baru" type="password"
                            :error-messages="errors.password" outlined dense required />
                        <v-text-field v-model="password_confirmation" label="Ulangi Password" type="password" outlined
                            dense required />
                        <v-btn size="large" :loading="loading" type="submit" color="primary" block class="mt-3">
                            Reset Password
                        </v-btn>
                    </v-form>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const token = route.query.token
const email = ref(route.query.email || '')

const password = ref('')
const password_confirmation = ref('')
const errors = ref({})
const message = ref('')
const loading = ref(false)

const submit = async () => {
    loading.value = true
    errors.value = {}

    try {
        await axios.post('/api/reset-password', {
            token,
            email: email.value,
            password: password.value,
            password_confirmation: password_confirmation.value
        })
        message.value = 'Password berhasil direset. Silakan login kembali.'
        password.value = ''
        password_confirmation.value = ''
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors
        } else {
            message.value = 'Gagal reset password. Coba lagi.'
        }
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
