<template>
    <HomeLayout>
        <v-container fluid class="d-flex align-center justify-center text-center hero-section">
            <v-row>
                <v-col cols="12">
                    <h1 class="text-h2 font-weight-bold mb-4">
                        Laravel 12
                    </h1>
                    <h2 class="text-h5 font-weight-regular mb-6">
                        Project ini menggunakan Laravel 12, Vue.js 3 dan Vuetify.js 3
                    </h2>

                    <div class="d-flex justify-center ga-4">
                        <v-btn color="primary" size="large" rounded="lg" router to="/login">
                            Mulai Sekarang
                        </v-btn>
                        <v-btn variant="outlined" size="large" rounded="lg">
                            Dokumentasi
                        </v-btn>
                    </div>
                </v-col>
            </v-row>
        </v-container>

    </HomeLayout>
</template>

<script setup>
import HomeLayout from '@/layouts/HomeLayout.vue'
</script>

<style scoped>
.hero-section {
  height: 100vh; /* full tinggi layar */
}
</style>
