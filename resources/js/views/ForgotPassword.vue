<template>
    <v-container fluid class="fill-height login-page">
        <v-row align="center" justify="center">
            <v-col cols="12" sm="8" md="4">
                <v-card max-width="500" elevation="8" class="pa-6">
                    <v-card-title class="text-h5 text-center font-weight-bold mb-4">Lupa Password</v-card-title>

                    <v-alert v-if="message" type="success" class="mb-4">
                        {{ message }}
                    </v-alert>

                    <v-form @submit.prevent="submit">
                        <v-text-field v-model="email" label="Email" type="email" :error-messages="errors.email" outlined
                            dense clearable required />
                        <v-btn size="large" :loading="loading" type="submit" color="primary" block class="mt-3 mb-5">
                            <v-icon>mdi-email</v-icon> Kirim Link Reset
                        </v-btn>
                    </v-form>
                    <RouterLink to="/login" class="text-decoration-none">
                        <i class="mdi mdi-chevron-left"></i> Back to Login
                    </RouterLink>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import axios from 'axios'

const email = ref('')
const errors = ref({})
const message = ref('')
const loading = ref(false)

const submit = async () => {
    loading.value = true
    errors.value = {}
    try {
        await axios.post('/api/forgot-password', { email: email.value })
        message.value = 'Link reset password telah dikirim ke email kamu.'
        email.value = ''
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors
        } else {
            message.value = 'Gagal mengirim link. Coba lagi.'
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
